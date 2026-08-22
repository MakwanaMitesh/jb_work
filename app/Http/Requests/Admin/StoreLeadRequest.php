<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leads.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile_number' => ['required', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'alternate_mobile_number' => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'agent_id' => ['nullable', 'exists:agents,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:new,contacted,in_progress,converted,lost'],
            'notes' => ['nullable', 'string', 'max:1000'],
            
            // New KYC Fields
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'aadhar_card' => ['nullable', 'string', 'max:30'],
            'pan_card' => ['nullable', 'string', 'max:30'],
            'udyam_registration' => ['nullable', 'string', 'max:50'],
            'education' => ['nullable', 'string', 'max:100'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            
            // ITR
            'itr_id' => ['nullable', 'string', 'max:100'],
            'itr_password' => ['nullable', 'string', 'max:100'],
            'itr_audited' => ['nullable', 'string', 'max:10'],
            'itr_ay_2026_27' => ['nullable', 'boolean'],
            'itr_ay_2025_26' => ['nullable', 'boolean'],
            'itr_ay_2024_25' => ['nullable', 'boolean'],
            
            // Bank Details (JSON Array)
            'bank_details' => ['nullable', 'array'],
            'bank_details.*.bank_name' => ['nullable', 'string', 'max:150'],
            'bank_details.*.account_number' => ['nullable', 'string', 'max:50'],
            'bank_details.*.account_type' => ['nullable', 'string', 'max:50'],
            'bank_details.*.ifsc_code' => ['nullable', 'string', 'max:30'],
            
            // Business Details
            'business_name' => ['nullable', 'string', 'max:150'],
            'constitution_of_business' => ['nullable', 'string', 'max:100'],
            'introduction' => ['nullable', 'string'],
            'business_address' => ['nullable', 'string'],
            'gst_applicable' => ['nullable', 'string', 'max:10'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'gst_id' => ['nullable', 'string', 'max:100'],
            'gst_password' => ['nullable', 'string', 'max:100'],
            'firm_name' => ['nullable', 'string', 'max:150'],
            'business_activity' => ['nullable', 'string', 'max:50'],
            'business_experience' => ['nullable', 'string', 'max:100'],
            'no_of_manpower' => ['nullable', 'string', 'max:50'],
            'business_location' => ['nullable', 'string', 'max:150'],
            'area_of_premises' => ['nullable', 'string', 'max:100'],
            'connectivity' => ['nullable', 'string', 'max:150'],
            
            // Required Loan
            'required_loan_amount' => ['nullable', 'string', 'max:50'],
            'cc_amount' => ['nullable', 'string', 'max:50'],
            'cc_details' => ['nullable', 'string'],
            'term_loan_amount' => ['nullable', 'string', 'max:50'],
            'term_loan_machinery_details' => ['nullable', 'string'],
            
            // Current Loans (JSON Array)
            'current_loans' => ['nullable', 'array'],
            'current_loans.*.bank_name' => ['nullable', 'string', 'max:150'],
            'current_loans.*.loan_type' => ['nullable', 'string', 'max:100'],
            'current_loans.*.loan_amount' => ['nullable', 'string', 'max:50'],
            'current_loans.*.disburse_date' => ['nullable', 'string', 'max:50'],
            'current_loans.*.emi' => ['nullable', 'string', 'max:50'],
            'current_loans.*.outstanding_amount' => ['nullable', 'string', 'max:50'],
            'current_loans.*.tenure' => ['nullable', 'string', 'max:50'],
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
