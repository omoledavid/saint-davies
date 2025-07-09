<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarHireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'car_id' => 'required|exists:cars,id',
            'pickup_datetime' => 'required|date',
            'return_datetime' => 'required|date',
            'pickup_location' => 'required|string',
            'dropoff_location' => 'nullable|string',
            'duration_in_days' => 'required|integer',
            'total_price' => 'nullable|numeric',
            'with_driver' => 'nullable|boolean',
            'insurance' => 'nullable|boolean',
        ];
    }
}
