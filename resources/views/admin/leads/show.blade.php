<x-admin-layout :title="$lead->name">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <span class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg font-bold shrink-0 border border-slate-200 dark:border-slate-700">LD</span>
            <div>
                <h1 class="text-2xl font-bold text-slate-950 dark:text-white leading-tight">{{ $lead->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $lead->email ?: 'No email' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @can('leads.edit')
                <a href="{{ route('admin.leads.edit', $lead) }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-semibold rounded-lg shadow-sm transition no-underline">
                    Edit Lead
                </a>
            @endcan
            <a href="{{ route('admin.leads.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm no-underline">
                Back
            </a>
        </div>
    </div>

    <!-- Status & Assignment Quick Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Status</span>
                @php
                    $color = match($lead->status) {
                        'new' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200/30 dark:border-blue-900/30',
                        'contacted' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200/30 dark:border-amber-900/30',
                        'in_progress' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/20 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/30',
                        'converted' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-200/30 dark:border-emerald-900/30',
                        'lost' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200/30 dark:border-slate-700/30',
                        default => 'bg-slate-100 text-slate-700'
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold mt-2 {{ $color }}">
                    {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                </span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Assigned Agent</span>
                <span class="block text-sm font-semibold text-slate-900 dark:text-white mt-1.5">
                    @if ($lead->agent)
                        <a href="{{ route('admin.agents.show', $lead->agent) }}" class="text-primary-600 hover:underline">{{ $lead->agent->name }}</a>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">City / Location</span>
                <span class="block text-sm font-semibold text-slate-900 dark:text-white mt-1.5">{{ $lead->city?->name ?: '—' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Lead Source / Created At</span>
                <span class="block text-sm font-semibold text-slate-900 dark:text-white mt-1.5">{{ $lead->source }} ({{ $lead->created_at?->format('d M Y') }})</span>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- KYC Details -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">KYC Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Date of Birth</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->date_of_birth?->format('d M Y') ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Gender</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->gender ?: '—' }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Address</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5 whitespace-pre-line">{{ $lead->address ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Aadhaar Card</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->aadhar_card ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">PAN Card</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->pan_card ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Udyam Registration</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->udyam_registration ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Education</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->education ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Mother Name</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->mother_name ?: '—' }}</span>
                    </div>
                </div>

                <!-- ITR Sub-section -->
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">ITR Profile</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">ITR ID</span>
                            <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->itr_id ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">ITR Password</span>
                            <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->itr_password ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">ITR Audited</span>
                            <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->itr_audited ?: '—' }}</span>
                        </div>
                        <div class="md:col-span-3">
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Assessment Years Filed</span>
                            <div class="flex gap-4 mt-1.5">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $lead->itr_ay_2026_27 ? 'bg-emerald-500' : 'bg-slate-300' }}"></span> A.Y. 2026-27
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $lead->itr_ay_2025_26 ? 'bg-emerald-500' : 'bg-slate-300' }}"></span> A.Y. 2025-26
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $lead->itr_ay_2024_25 ? 'bg-emerald-500' : 'bg-slate-300' }}"></span> A.Y. 2024-25
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Details -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Business Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Business Name</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->business_name ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Constitution of Business</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->constitution_of_business ?: '—' }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Introduction</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5 whitespace-pre-line">{{ $lead->introduction ?: '—' }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Business Address</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5 whitespace-pre-line">{{ $lead->business_address ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Firm Name</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->firm_name ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Business Activity</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->business_activity ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Business Experience</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->business_experience ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">No. of Manpower</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->no_of_manpower ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Location</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->business_location ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Area of Premises</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->area_of_premises ?: '—' }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Connectivity</span>
                        <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->connectivity ?: '—' }}</span>
                    </div>
                </div>

                <!-- GST Sub-section -->
                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-3">GST Registration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Registered</span>
                            <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->gst_applicable ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">GST Number</span>
                            <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->gst_number ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">GST Portal ID</span>
                            <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->gst_id ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">GST Password</span>
                            <span class="block font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->gst_password ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Bank Details -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Current Bank Details</h3>
                
                @php
                    $hasBankDetails = false;
                    if (is_array($lead->bank_details)) {
                        foreach ($lead->bank_details as $bank) {
                            if (!empty($bank['bank_name']) || !empty($bank['account_number'])) {
                                $hasBankDetails = true;
                                break;
                            }
                        }
                    }
                @endphp

                @if ($hasBankDetails)
                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                <tr>
                                    <th class="p-3 font-semibold w-12 text-center">SR</th>
                                    <th class="p-3 font-semibold">Bank Name</th>
                                    <th class="p-3 font-semibold">A/C Number</th>
                                    <th class="p-3 font-semibold">A/C Type</th>
                                    <th class="p-3 font-semibold">IFSC Code</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-150 dark:divide-slate-800">
                                @foreach ($lead->bank_details as $index => $bank)
                                    @if (!empty($bank['bank_name']) || !empty($bank['account_number']))
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                                            <td class="p-3 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                                            <td class="p-3 font-medium text-slate-900 dark:text-white">{{ $bank['bank_name'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $bank['account_number'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $bank['account_type'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $bank['ifsc_code'] ?: '—' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-slate-400 dark:text-slate-500">No bank details recorded.</p>
                @endif
            </div>

            <!-- Current Loan Details -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Current Loan Details</h3>
                
                @php
                    $hasLoans = false;
                    if (is_array($lead->current_loans)) {
                        foreach ($lead->current_loans as $loan) {
                            if (!empty($loan['bank_name']) || !empty($loan['loan_amount'])) {
                                $hasLoans = true;
                                break;
                            }
                        }
                    }
                @endphp

                @if ($hasLoans)
                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                <tr>
                                    <th class="p-3 font-semibold w-12 text-center">SR</th>
                                    <th class="p-3 font-semibold">Bank Name</th>
                                    <th class="p-3 font-semibold">Loan Type</th>
                                    <th class="p-3 font-semibold">Loan Amount</th>
                                    <th class="p-3 font-semibold">Disburse Date</th>
                                    <th class="p-3 font-semibold">EMI</th>
                                    <th class="p-3 font-semibold">Outstanding</th>
                                    <th class="p-3 font-semibold">Tenure</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-150 dark:divide-slate-800">
                                @foreach ($lead->current_loans as $index => $loan)
                                    @if (!empty($loan['bank_name']) || !empty($loan['loan_amount']))
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10">
                                            <td class="p-3 text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                                            <td class="p-3 font-medium text-slate-900 dark:text-white">{{ $loan['bank_name'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $loan['loan_type'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $loan['loan_amount'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $loan['disburse_date'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $loan['emi'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $loan['outstanding_amount'] ?: '—' }}</td>
                                            <td class="p-3 text-slate-600 dark:text-slate-300">{{ $loan['tenure'] ?: '—' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-slate-400 dark:text-slate-500">No current loans recorded.</p>
                @endif
            </div>

        </div>

        <!-- Right 1 Col: Required Loans & Notes -->
        <div class="space-y-6">
            
            <!-- Required Loan Details -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Required Loan Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Total Required Loan Amount</span>
                        <span class="block text-lg font-bold text-primary-600 dark:text-primary-400 mt-0.5">{{ $lead->required_loan_amount ?: '—' }}</span>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        <div>
                            <span class="block text-xs font-semibold text-slate-800 dark:text-slate-200">1) CC (Cash Credit) Amount</span>
                            <span class="block text-sm font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->cc_amount ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">CC Details</span>
                            <p class="text-sm text-slate-600 dark:text-slate-350 mt-1 whitespace-pre-line">{{ $lead->cc_details ?: '—' }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        <div>
                            <span class="block text-xs font-semibold text-slate-800 dark:text-slate-200">2) Term Loan Amount</span>
                            <span class="block text-sm font-semibold text-slate-900 dark:text-white mt-0.5">{{ $lead->term_loan_amount ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-500 dark:text-slate-400">Machinery Details</span>
                            <p class="text-sm text-slate-600 dark:text-slate-350 mt-1 whitespace-pre-line">{{ $lead->term_loan_machinery_details ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes / Requirements -->
            @if ($lead->notes)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">Notes / Requirements</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $lead->notes }}</p>
                </div>
            @endif

        </div>

    </div>
</x-admin-layout>
