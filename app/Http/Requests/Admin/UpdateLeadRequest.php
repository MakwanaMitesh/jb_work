<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leads.edit');
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
            'bank_id' => ['nullable', 'exists:banks,id'],
            'loan_product_id' => ['required', 'exists:loan_products,id'],
            'constitution_id' => ['required', 'exists:customer_constitutions,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:new,contacted,in_progress,converted,lost,visit_pending,visit_completed,documentation_pending,documentation_in_progress,documentation_completed,under_process,approved,rejected,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            
            // New KYC Fields
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'aadhar_card' => ['nullable', 'string', 'max:30'],
            'pan_card' => ['nullable', 'string', 'max:30'],
            'udyam_registration' => ['nullable', 'string', 'max:50'],
            'fssai_license' => ['nullable', 'string', 'max:100'],
            'education' => ['nullable', 'string', 'max:100'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            
            // ITR
            'itr_id' => ['nullable', 'string', 'max:100'],
            'itr_password' => ['nullable', 'string', 'max:100'],
            'itr_audited' => ['nullable', 'string', 'max:10'],
            'itr_ay_2026_27' => ['nullable', 'boolean'],
            'itr_ay_2025_26' => ['nullable', 'boolean'],
            'itr_ay_2024_25' => ['nullable', 'boolean'],
            'itr_details' => ['nullable', 'array'],
            'itr_details.*.assessment_year' => ['nullable', 'string', 'max:100'],
            'itr_details.*.itr_audited' => ['nullable', 'string', 'max:10'],
            'itr_details.*.audit_report' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'itr_details.*.itr_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'itr_details.*.computation' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'itr_details.*.itr_form' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            
            // Bank Details (JSON Array)
            'bank_details' => ['nullable', 'array'],
            'bank_details.*.bank_name' => ['nullable', 'string', 'max:150'],
            'bank_details.*.account_number' => ['nullable', 'string', 'max:50'],
            'bank_details.*.account_type' => ['nullable', 'string', 'max:50'],
            'bank_details.*.ifsc_code' => ['nullable', 'string', 'max:30'],
            
            // Bank Loan Verification Details
            'bank_loan_details' => ['nullable', 'array'],
            
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
            'land_and_factory_building' => ['nullable', 'string'],
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $productId = $this->input('loan_product_id');
            $constitutionId = $this->input('constitution_id');
            
            if ($productId) {
                $product = \App\Models\LoanProduct::find($productId);
                if (!$product || $product->status !== 'active') {
                    $validator->errors()->add('loan_product_id', 'The selected loan product is inactive or invalid.');
                }
            }

            if ($constitutionId) {
                $constitution = \App\Models\CustomerConstitution::find($constitutionId);
                if (!$constitution || $constitution->status !== 'active') {
                    $validator->errors()->add('constitution_id', 'The selected customer constitution is inactive or invalid.');
                }
            }

            if ($productId && $constitutionId) {
                $exists = \DB::table('loan_product_constitutions')
                    ->where('loan_product_id', $productId)
                    ->where('constitution_id', $constitutionId)
                    ->where('status', 'active')
                    ->exists();
                if (!$exists) {
                    $validator->errors()->add('loan_product_id', 'Selected loan product is not available for the selected customer constitution.');
                }
            }

            $this->validateLeadDocuments($validator);
        });
    }

    protected function validateLeadDocuments($validator)
    {
        $lead = $this->route('lead');
        $documentTypes = \App\Models\DocumentType::where('status', 'active')->get();

        foreach ($documentTypes as $docType) {
            $code = $docType->code;
            $id = $docType->id;
            $allowedMimes = implode(',', $docType->allowed_file_types ?? ['pdf', 'jpg', 'jpeg', 'png']);
            $maxSizeKb = $docType->max_file_size_kb;

            if ($docType->has_front_back) {
                $frontFile = $this->file("documents.{$code}.front") ?? $this->file("documents.{$id}.front");
                $backFile = $this->file("documents.{$code}.back") ?? $this->file("documents.{$id}.back");

                $hasExistingFront = $lead && $lead->leadDocuments()->where('document_type_id', $docType->id)->where('side', 'front')->exists();
                $hasExistingBack = $lead && $lead->leadDocuments()->where('document_type_id', $docType->id)->where('side', 'back')->exists();

                if ($docType->is_required) {
                    if (!$frontFile && !$hasExistingFront) {
                        $validator->errors()->add("documents.{$code}.front", "The {$docType->name} (Front side) is required.");
                    }
                    if (!$backFile && !$hasExistingBack) {
                        $validator->errors()->add("documents.{$code}.back", "The {$docType->name} (Back side) is required.");
                    }
                }

                if ($frontFile) {
                    $this->validateUploadedFile($validator, "documents.{$code}.front", $frontFile, $allowedMimes, $maxSizeKb, "{$docType->name} (Front side)");
                }
                if ($backFile) {
                    $this->validateUploadedFile($validator, "documents.{$code}.back", $backFile, $allowedMimes, $maxSizeKb, "{$docType->name} (Back side)");
                }
            } elseif ($docType->allow_multiple) {
                $files = $this->file("documents.{$code}.files") ?? $this->file("documents.{$id}.files") ?? $this->file("documents.{$code}") ?? [];
                if (!is_array($files) && $files) {
                    $files = [$files];
                }

                $hasExisting = $lead && $lead->leadDocuments()->where('document_type_id', $docType->id)->exists();

                if ($docType->is_required && empty($files) && !$hasExisting) {
                    $validator->errors()->add("documents.{$code}", "At least one {$docType->name} document is required.");
                }

                foreach ($files as $idx => $f) {
                    if ($f) {
                        $this->validateUploadedFile($validator, "documents.{$code}.{$idx}", $f, $allowedMimes, $maxSizeKb, "{$docType->name} file #" . ($idx + 1));
                    }
                }
            } else {
                $singleFile = $this->file("documents.{$code}.file") ?? $this->file("documents.{$id}.file") ?? $this->file("documents.{$code}") ?? $this->file("documents.{$id}");
                $hasExisting = $lead && $lead->leadDocuments()->where('document_type_id', $docType->id)->exists();

                if ($docType->is_required && !$singleFile && !$hasExisting) {
                    $validator->errors()->add("documents.{$code}", "The {$docType->name} document is required.");
                }

                if ($singleFile) {
                    $this->validateUploadedFile($validator, "documents.{$code}", $singleFile, $allowedMimes, $maxSizeKb, $docType->name);
                }
            }
        }
    }

    protected function validateUploadedFile($validator, $key, $file, $allowedMimes, $maxSizeKb, $label)
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $allowedArray = array_map('trim', explode(',', $allowedMimes));

        if (!in_array($ext, $allowedArray)) {
            $validator->errors()->add($key, "The {$label} must be a file of type: " . implode(', ', $allowedArray) . ".");
        }

        if (($file->getSize() / 1024) > $maxSizeKb) {
            $maxMb = round($maxSizeKb / 1024, 1);
            $validator->errors()->add($key, "The {$label} size must not exceed {$maxMb}MB.");
        }
    }
}
