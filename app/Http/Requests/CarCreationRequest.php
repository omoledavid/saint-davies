<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarCreationRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'nullable|string|max:255',
            'condition' => 'nullable|string|max:255',
            'transmission' => 'nullable|string|max:255',
            'fuel_type' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'type' => 'required|string|in:rent,sale',
            'rent_frequency' => 'nullable|string|in:daily,weekly,monthly',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'is_available' => 'nullable|boolean',
        ];
    }
}
