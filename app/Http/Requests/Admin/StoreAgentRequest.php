<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('agent.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:agents,email'],
            'mobile_number' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'profile_photo' => ['nullable', 'image', 'max:2048'],

            // Qualifications
            'qualification' => ['nullable', 'string', 'max:255'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:5120'],
            'alternate_mobile_number' => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],

            // KYC
            'aadhaar_card_number' => ['nullable', 'string', 'max:50'],
            'aadhaar_photo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'pan_card_number' => ['nullable', 'string', 'max:50'],
            'pan_photo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],

            // Bank
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc_code' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'bank_account_holder_name' => ['nullable', 'string', 'max:150'],
            'bank_cheque_photo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_number.regex' => 'The mobile number must be a valid phone number (e.g. +919876543210).',
            'alternate_mobile_number.regex' => 'The alternate mobile number must be a valid phone number (e.g. +919876543210).',
        ];
    }
}
