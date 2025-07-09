<?php

namespace App\Http\Requests;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $property = Property::findOrFail($this->route('id'));
            return $property->manager_id === auth()->id();
        }
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
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_available' => 'required|boolean',
            'sop' => 'nullable|string',// size of property
            'image' => 'nullable|string',
            'video' => 'nullable|string',
            // 'status' => 'nullable|in:' . implode(',', array_column(PropertyStatus::cases(), 'value')),
            'property_type' => 'nullable|string',
            'property_category' => 'required|string',
        ];
    }
}
