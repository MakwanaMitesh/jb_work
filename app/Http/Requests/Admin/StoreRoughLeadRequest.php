<?php

namespace App\Http\Requests\Admin;

use App\Models\RoughLead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoughLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('rough_leads.create');
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'mobile_number' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'alternate_mobile_number' => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'loan_requirement' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:100'],
            'assigned_employee_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string', Rule::in(array_keys(RoughLead::STATUSES))],
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
