<?php

namespace App\Http\Controllers;

use App\Http\Resources\HotelBookingResource;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Services\PaystackService;
use App\Traits\ApiResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HotelBookingController extends Controller
{
    use ApiResponses;
    protected $paystackService;
    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }
    public function myBookings()
    {
        $bookings = HotelBooking::where('user_id', auth()->id())->latest()->get();
        return $this->ok('Hotel bookings fetched successfully', HotelBookingResource::collection($bookings));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'room_id' => 'required|exists:hotel_rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
            'number_of_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
        ]);
        $reference = 'booking_' . $validatedData['room_id'] . '_' . time();
        $validatedData['paystack_reference'] = $reference;
        $validatedData['user_id'] = auth()->id();
        $validatedData['status'] = 'pending';
        $validatedData['is_paid'] = false;
        // Check if the room is already booked for the given dates
        $overlappingBooking = HotelBooking::where('room_id', $validatedData['room_id'])
            ->where(function ($query) use ($validatedData) {
                $query->where(function ($q) use ($validatedData) {
                    $q->where('check_in_date', '<', $validatedData['check_out_date'])
                        ->where('check_out_date', '>', $validatedData['check_in_date']);
                });
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($overlappingBooking) {
            return $this->error('This room is already booked for the selected dates.', 409);
        }

        // Lock the room so another user can't book it while payment is pending
        $room = HotelRoom::find($validatedData['room_id']);
        if (!$room->is_available) {
            return $this->error('This room is currently unavailable.', 409);
        }
        $hotelBooking = HotelBooking::create($validatedData);
        $checkIn = Carbon::parse($validatedData['check_in_date']);
        $checkOut = Carbon::parse($validatedData['check_out_date']);

        $days = $checkOut->diffInDays($checkIn);

        $days = max(1, $checkIn->diffInDays($checkOut));
        $totalAmount = $hotelBooking->room->roomCategory->price * $days;
        $hotelBooking->update(['total_amount' => $totalAmount]);
        return $this->ok('Hotel booking created successfully', new HotelBookingResource($hotelBooking));
    }
    public function pay(Request $request)
    {
        $validatedData = $request->validate([
            'booking_id' => 'required|exists:hotel_bookings,id',
            'callback_url' => 'nullable|url',
        ]);
        $booking = HotelBooking::find($validatedData['booking_id']);
        if (!$booking) {
            return $this->error('Booking not found', 404);
        }
        $paystackData = [
            'email' => auth()->user()->email,
            'amount' => $booking->total_amount * 100,
            'currency' => 'NGN',
            'reference' => $booking->paystack_reference,
            'callback_url' => url('api/verify-payment/hotel-booking/' . $booking->paystack_reference),
        ];
        $response = $this->paystackService->initializeTransaction($paystackData);
        if (!$response->successful()) {
            return $this->error('Payment initialization failed', 500, $response->body());
        }
        // $booking->update(['paystack_reference' => $response->json()['data']['reference']]);
        return $this->ok('Payment initialized successfully', $response->json());
    }
}
