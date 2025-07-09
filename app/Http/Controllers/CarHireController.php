<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarHireRequest;
use App\Http\Resources\CarHireResource;
use App\Models\Car;
use App\Models\CarHire;
use App\Services\PaystackService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class CarHireController extends Controller
{
    use ApiResponses;
    protected $paystackService;
    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }
    public function myHires()
    {
        $hires = CarHire::where('user_id', auth()->id())->latest()->get();
        return $this->ok('Car hires fetched successfully', CarHireResource::collection($hires));
    }
    public function store(CarHireRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = auth()->id();
        $validatedData['status'] = 'pending';
        $validatedData['is_paid'] = false;
        $reference = 'car_hire_' . $validatedData['car_id'] . '_' . time();
        $validatedData['paystack_reference'] = $reference;
        $car = Car::find($validatedData['car_id']);
        $validatedData['total_price'] = $validatedData['duration_in_days'] * $car->price;
        $carHire = CarHire::create($validatedData);
        return $this->ok('Car hire created successfully', new CarHireResource($carHire));
    }
    public function pay(Request $request)
    {
        $validatedData = $request->validate([
            'hire_id' => 'required|exists:car_hires,id',
            'callback_url' => 'nullable|url',
        ]);
        $hire = CarHire::find($validatedData['hire_id']);
        if (!$hire) {
            return $this->error('Car hire not found', 404);
        }
        $paystackData = [
            'email' => auth()->user()->email,
            'amount' => $hire->total_price * 100,
            'currency' => 'NGN',
            'reference' => $hire->paystack_reference,
            'callback_url' => url('api/verify-payment/car-hire/' . $hire->paystack_reference),
        ];
        $response = $this->paystackService->initializeTransaction($paystackData);
        if (!$response->successful()) {
            return $this->error('Payment initialization failed', 500, $response->body());
        }
        return $this->ok('Payment initialized successfully', $response->json());
    }
}
