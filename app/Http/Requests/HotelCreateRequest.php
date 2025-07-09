<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelCreateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'video_url' => 'nullable|url',
            'price' => 'nullable|numeric|min:0',
            'policies' => 'nullable|string',
            'cancellation_policy' => 'nullable|string',
            'check_in_time' => 'nullable|string',
            'amenities' => 'nullable|array',
        ];
    }
}
