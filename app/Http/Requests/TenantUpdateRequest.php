<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantUpdateRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:tenancies,email',
            'phone' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'income' => 'nullable|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'id_type' => 'nullable|string|max:255',
            'id_front_image' => 'nullable|string|max:255',
            'id_back_image' => 'nullable|string|max:255',
            'user_image' => 'nullable|string|max:255',
            'property_unit_id' => 'nullable|exists:property_units,id',
            'rent_start' => 'nullable|date',
            'rent_end' => 'nullable|date|after_or_equal:rent_start',
            'is_active' => 'boolean',
        ];
    }
}
