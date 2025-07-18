<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyUnitUpdateRequest extends FormRequest
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
            'description' => 'nullable|string',
            'rent_amount' => 'nullable|numeric|min:0',
            'rent_frequency' => 'nullable|string|in:monthly,yearly,weekly,quarterly',
            'is_occupied' => 'nullable|boolean',
            'image' => 'nullable|string',
            'video' => 'nullable|url',
            'agreement_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:2048',
            'payment_receipt' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:2048',
            'bed_room' => 'nullable|integer|min:1',
            'bath_room' => 'nullable|integer|min:1',
            'parking' => 'nullable|boolean',
            'security' => 'nullable|boolean',
            'water' => 'nullable|boolean',
            'electricity' => 'nullable|boolean',
            'internet' => 'nullable|boolean',
            'tv' => 'nullable|boolean',
        ];
    }
}
