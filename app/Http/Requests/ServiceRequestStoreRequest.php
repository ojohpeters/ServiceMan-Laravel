<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequestStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isClient();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'serviceman_id' => 'required|exists:users,id',
            'booking_date' => 'required|date|after:today',
            'is_emergency' => 'boolean',
            'client_address' => 'required|string|max:500',
            'service_description' => 'required|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a service category.',
            'serviceman_id.required' => 'Please select a serviceman.',
            'booking_date.required' => 'Please select a preferred service date.',
            'booking_date.after' => 'The booking date must be in the future.',
            'client_address.required' => 'Please provide the service address.',
            'client_address.max' => 'The service address cannot exceed 500 characters.',
            'service_description.required' => 'Please describe the service needed.',
            'service_description.max' => 'The service description cannot exceed 1000 characters.',
        ];
    }
}

