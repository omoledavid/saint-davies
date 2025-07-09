<?php

namespace App\Http\Controllers;

use App\Http\Resources\CarHireResource;
use App\Http\Resources\HotelBookingResource;
use App\Models\CarHire;
use App\Models\HotelBooking;
use App\Services\PaystackService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    use ApiResponses;
    protected $paystackService;
    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }
    public function hotelBooking($reference)
    {
        $verification = $this->paystackService->verifyTransaction($reference);
        if (!$verification->successful()) {
            return $this->error('Payment verification failed', 500);
        }
        $booking = HotelBooking::where('paystack_reference', $reference)->first();
        if (!$booking) {
            return $this->error('Booking not found', 404);
        }
        $booking->update(['is_paid' => true, 'status' => 'confirmed']);
        return $this->ok('Payment verified successfully', new HotelBookingResource($booking));
    }
    public function carHire($reference)
    {
        $verification = $this->paystackService->verifyTransaction($reference);
        if (!$verification->successful()) {
            return $this->error('Payment verification failed', 500);
        }
        $hire = CarHire::where('paystack_reference', $reference)->first();
        if (!$hire) {
            return $this->error('Car hire not found', 404);
        }
        $hire->update(['is_paid' => true, 'status' => 'confirmed']);
        return $this->ok('Payment verified successfully', new CarHireResource($hire));
    }
}
