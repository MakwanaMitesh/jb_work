{{-- Application Form tab panel. $lead may be null (Create Lead). The controls are ordinary members of the
     lead <form> (names af[...]), so one submit saves the lead and its Application Form together. --}}
@php
    use App\Support\ApplicationFormData as AF;
    use Illuminate\Support\HtmlString;

    $lead = $lead ?? null;
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag;
    $exists = (bool) $lead?->exists;
    $af = $exists ? AF::for($lead) : AF::normalize([]);
    $saved = $exists ? $lead->applicationForm : null;
    $canWrite = auth()->user()->can($saved?->saved_at ? 'application_form.update' : 'application_form.create');
    $generatedFile = $saved?->generated_path ? $saved : null;
    $afErrors = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'af.'))->map(fn ($k) => $errors->first($k))->unique()->values();

    $e = fn ($v) => e((string) $v);
    $name = fn (string $path) => 'af[' . str_replace('.', '][', $path) . ']';
    $val = fn (string $path) => old('af.' . $path, data_get($af, $path));
    $attrs = $canWrite ? '' : 'disabled';

    $subTabs = ['t1' => 'Table 1', 't2' => 'Table 2', 't3' => 'Table 3'];
@endphp

<style>
    #application-form-panel {
        --af-border-color: #000000;
        --af-header-bg: #f8fafc;
        --af-section-bg: #e5e7eb;
        --af-cell-font: 'Times New Roman', Times, Georgia, serif;
    }
    #application-form-panel .af-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 16px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    #application-form-panel .af-excel-table {
        width: 100%;
        min-width: 1020px;
        border-collapse: collapse;
        table-layout: fixed;
        font-family: var(--af-cell-font);
        font-size: 13px;
        color: #000000;
        line-height: 1.25;
        background: #ffffff;
    }
    #application-form-panel .af-excel-table td,
    #application-form-panel .af-excel-table th {
        border: 1px solid var(--af-border-color);
        padding: 4px 6px;
        vertical-align: middle;
        word-break: break-word;
    }
    #application-form-panel .af-excel-table th {
        font-weight: bold;
        background-color: var(--af-header-bg);
    }
    #application-form-panel .af-section-banner {
        background-color: var(--af-section-bg);
        font-weight: bold;
        font-size: 13.5px;
        padding: 6px 10px;
        border: 1px solid var(--af-border-color);
        color: #000000;
    }
    #application-form-panel .af-sub-banner {
        font-size: 12px;
        font-weight: normal;
        background-color: #fbfcfd;
    }
    #application-form-panel .af-label {
        font-weight: 600;
    }
    #application-form-panel .af-bold {
        font-weight: bold;
    }
    #application-form-panel .af-cell-input {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        font-family: var(--af-cell-font);
        font-size: 13px;
        color: #000000;
        padding: 2px 4px;
        border-radius: 0;
        outline: none;
        box-sizing: border-box;
        transition: background-color 0.15s, border-color 0.15s;
    }
    #application-form-panel .af-cell-input:focus {
        background: #fffde7;
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }
    #application-form-panel .af-cell-input:hover:not(:focus) {
        background: rgba(0, 0, 0, 0.02);
        border-color: #cbd5e1;
    }
    #application-form-panel .af-cell-textarea {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        font-family: var(--af-cell-font);
        font-size: 13px;
        color: #000000;
        padding: 2px 4px;
        border-radius: 0;
        outline: none;
        resize: vertical;
        box-sizing: border-box;
        transition: background-color 0.15s, border-color 0.15s;
    }
    #application-form-panel .af-cell-textarea:focus {
        background: #fffde7;
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }
    #application-form-panel .af-cell-textarea:hover:not(:focus) {
        background: rgba(0, 0, 0, 0.02);
        border-color: #cbd5e1;
    }
    #application-form-panel .af-cell-select {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        font-family: var(--af-cell-font);
        font-size: 13px;
        color: #000000;
        padding: 2px 4px;
        border-radius: 0;
        outline: none;
        box-sizing: border-box;
        cursor: pointer;
    }
    #application-form-panel .af-cell-select:focus {
        background: #fffde7;
        border-color: #2563eb;
    }
    #application-form-panel .af-choice-cell {
        padding: 2px 6px;
    }
    #application-form-panel .af-choice-label {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin: 0;
        cursor: pointer;
        font-family: var(--af-cell-font);
        font-size: 13px;
        font-weight: normal;
        padding: 1px 4px;
        border-radius: 2px;
        transition: background-color 0.15s;
    }
    #application-form-panel .af-choice-label input[type="radio"],
    #application-form-panel .af-choice-label input[type="checkbox"] {
        margin: 0;
        cursor: pointer;
    }
    #application-form-panel .af-choice-label:has(input:checked) {
        background-color: #fff176;
        font-weight: bold;
    }
    #application-form-panel .af-photo-box {
        height: 110px;
        border: 1px dashed #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 13px;
        font-weight: bold;
        background: #f8fafc;
        margin: 2px;
    }
    #application-form-panel .af-cell-input:disabled,
    #application-form-panel .af-cell-textarea:disabled,
    #application-form-panel .af-cell-select:disabled {
        background: #f1f5f9;
        color: #475569;
        cursor: not-allowed;
    }
</style>

<div id="application-form-panel" class="tab-panel jb-hidden">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-2 mb-3">
        <div>
            <h3 class="jb-section-title m-0">Application Form</h3>
            <p class="jb-muted mb-0" style="font-size:var(--text-xs)">Loan application (Mudra), laid out like the bank's three-sheet Excel form. Fields that exist in the Details Sheet fill in from it automatically; saved together with the lead.</p>
        </div>
        @if ($exists)
            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('application_form.generate')
                    <button type="submit" form="application-form-generate" class="btn btn-outline-primary btn-sm" id="af-generate-btn">Generate Application Form</button>
                @endcan
                @can('application_form.download')
                    @if ($generatedFile)
                        <a href="{{ route('admin.leads.application-form.download', $lead) }}" class="btn btn-success btn-sm">Download Application Form</a>
                    @endif
                @endcan
            </div>
        @endif
    </div>

    @if ($generatedFile?->generated_at)
        <p class="jb-muted mb-3" style="font-size:var(--text-xs)">Last generated {{ $generatedFile->generated_at->format('d M Y, h:i A') }}. Regenerate after saving changes to refresh the file.</p>
    @endif

    @if ($errors->has('af_generate'))
        <div class="alert alert-warning py-2 mb-3" role="alert">{{ $errors->first('af_generate') }}</div>
    @endif
    @if ($afErrors->isNotEmpty())
        <div class="alert alert-danger py-2 mb-3" role="alert">
            <strong>Please correct the following:</strong>
            <ul class="mb-0">@foreach ($afErrors as $msg)<li>{{ $msg }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="d-flex gap-2 mb-3" role="tablist" id="af-sub-tabs">
        @foreach ($subTabs as $key => $label)
            <button type="button" class="btn btn-sm {{ $loop->first ? 'btn-primary' : 'btn-outline-secondary' }} af-sub-btn" data-af-target="af-{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>

    {{-- ===================== TABLE 1 ===================== --}}
    <div id="af-t1" class="af-sub-panel">
        <div class="af-table-wrapper">
            <table class="af-excel-table">
                <colgroup>
                    <col style="width: 7.30%">
                    <col style="width: 7.57%">
                    <col style="width: 2.40%">
                    <col style="width: 1.16%">
                    <col style="width: 5.97%">
                    <col style="width: 1.34%">
                    <col style="width: 1.07%">
                    <col style="width: 2.94%">
                    <col style="width: 1.60%">
                    <col style="width: 5.52%">
                    <col style="width: 5.79%">
                    <col style="width: 1.60%">
                    <col style="width: 1.60%">
                    <col style="width: 4.27%">
                    <col style="width: 5.52%">
                    <col style="width: 1.78%">
                    <col style="width: 2.40%">
                    <col style="width: 4.63%">
                    <col style="width: 1.60%">
                    <col style="width: 5.97%">
                    <col style="width: 1.07%">
                    <col style="width: 1.60%">
                    <col style="width: 1.60%">
                    <col style="width: 2.85%">
                    <col style="width: 2.49%">
                    <col style="width: 1.34%">
                    <col style="width: 2.85%">
                    <col style="width: 1.34%">
                    <col style="width: 4.27%">
                    <col style="width: 1.34%">
                    <col style="width: 2.94%">
                    <col style="width: 4.27%">
                </colgroup>
                <tbody>
                    {{-- Row 1: Title --}}
                    <tr>
                        <td colspan="32" class="text-center af-bold" style="font-size:15px;padding:6px;border-bottom:1px solid #000;">
                            Application form for Loans above Rs 50,000
                        </td>
                    </tr>
                    {{-- Row 2: Logos --}}
                    <tr>
                        <td colspan="16" style="vertical-align:middle;padding:6px 12px;border-right:none;">
                            <img src="{{ asset('upload/Application_form/sbi_logo.jpeg') }}" alt="State Bank of India" style="height:36px;object-fit:contain;">
                        </td>
                        <td colspan="16" style="vertical-align:middle;text-align:right;padding:6px 12px;border-left:none;">
                            <img src="{{ asset('upload/Application_form/mudra_logo.jpeg') }}" alt="Pradhan Mantri Mudra Yojana" style="height:36px;object-fit:contain;">
                        </td>
                    </tr>
                    {{-- Row 3: Bank Name --}}
                    <tr>
                        <td colspan="32" class="text-center" style="font-size:13px;padding:4px;">
                            Name of the Bank:<strong>STATE BANK OF INDIA</strong>
                        </td>
                    </tr>
                    {{-- Row 4: Mudra Header --}}
                    <tr>
                        <td colspan="32" class="text-center" style="font-size:13px;padding:5px;line-height:1.3;">
                            <div class="af-bold" style="font-size:14px;">LOAN APPLICATION FORM &nbsp; PRADHAN MANTRI MUDRA YOJANA</div>
                            <div style="font-size:11.5px;color:#333;">( To be submitted along with documents as per the check list )</div>
                        </td>
                    </tr>
                    {{-- Row 5: Section A --}}
                    <tr>
                        <td colspan="32" class="af-section-banner">
                            A. For Office Use:
                        </td>
                    </tr>
                    {{-- Row 6: Office Use Headers --}}
                    <tr style="background:#f8fafc;">
                        <td colspan="5" class="af-bold">Enterprise Name</td>
                        <td colspan="9" class="af-bold">Application Sl. No.</td>
                        <td colspan="9" class="af-bold">Name of the Branch</td>
                        <td colspan="9" class="af-bold text-center">Category</td>
                    </tr>
                    {{-- Row 7: Office Use Inputs --}}
                    <tr>
                        <td colspan="5">
                            <input type="text" id="af_enterprise_name" name="af[enterprise_name]" {{ $attrs }} value="{{ $val('enterprise_name') }}" class="af-cell-input fw-semibold" placeholder="Enterprise Name">
                        </td>
                        <td colspan="9">
                            <input type="text" id="af_application_serial_no" name="af[application_serial_no]" value="{{ $val('application_serial_no') }}" class="af-cell-input" placeholder="Application Sl. No." {{ $attrs }}>
                        </td>
                        <td colspan="9">
                            <input type="text" id="af_branch_name" name="af[branch_name]" value="{{ $val('branch_name') }}" class="af-cell-input" placeholder="Name of Branch" {{ $attrs }}>
                        </td>
                        <td colspan="9">
                            <select id="af_category" name="af[category]" class="af-cell-select text-center fw-semibold" {{ $attrs }}>
                                <option value="">— Select Category —</option>
                                @foreach (AF::CATEGORIES as $cat)
                                    <option value="{{ $cat }}" @selected($val('category') === $cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    {{-- Row 8: Section B --}}
                    <tr>
                        <td colspan="32" class="af-section-banner">
                            B. Business Information:
                        </td>
                    </tr>
                    {{-- Row 9: Enterprise Name in Section B --}}
                    <tr>
                        <td colspan="3" class="af-label af-bold">Name of the<br>Enterprise</td>
                        <td colspan="29">
                            <input type="text" id="af_enterprise_name_b" value="{{ $val('enterprise_name') }}" class="af-cell-input fw-bold" style="font-size:15px;" placeholder="Enterprise Name" {{ $attrs }}>
                        </td>
                    </tr>
                    {{-- Row 10: Constitution --}}
                    <tr>
                        <td colspan="2" class="af-label af-bold">Constitution</td>
                        <td colspan="7" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[constitution]" value="Proprietary" {{ $attrs }} @checked($val('constitution') === 'Proprietary')>
                                Proprietary
                            </label>
                        </td>
                        <td colspan="4" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[constitution]" value="Partnership" {{ $attrs }} @checked($val('constitution') === 'Partnership')>
                                Partnership
                            </label>
                        </td>
                        <td colspan="3" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[constitution]" value="Pvt. Ltd." {{ $attrs }} @checked($val('constitution') === 'Pvt. Ltd.')>
                                Pvt. Ltd.
                            </label>
                        </td>
                        <td colspan="6" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[constitution]" value="Ltd. Company" {{ $attrs }} @checked($val('constitution') === 'Ltd. Company')>
                                Ltd. Company
                            </label>
                        </td>
                        <td colspan="10" class="af-choice-cell">
                            <div class="d-flex align-items-center gap-1">
                                <label class="af-choice-label text-nowrap">
                                    <input type="radio" name="af[constitution]" value="Others" {{ $attrs }} @checked($val('constitution') === 'Others')>
                                    Any Others (specify)
                                </label>
                                <input type="text" id="af_constitution_other" name="af[constitution_other]" value="{{ $val('constitution_other') }}" class="af-cell-input flex-grow-1" placeholder="Specify" {{ $attrs }}>
                            </div>
                        </td>
                    </tr>
                    {{-- Rows 11, 12, 13: Address, State, PIN, Premises --}}
                    <tr>
                        <td colspan="3" rowspan="3" class="af-label af-bold text-center" style="vertical-align:middle;">
                            Current Business Address
                        </td>
                        <td colspan="29">
                            <textarea id="af_business_address" name="af[business_address]" rows="2" class="af-cell-textarea" placeholder="Enter current business address" {{ $attrs }}>{{ $val('business_address') }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8" class="af-label af-bold">State</td>
                        <td colspan="6">
                            <input type="text" id="af_state" name="af[state]" value="{{ $val('state') }}" class="af-cell-input" placeholder="State" {{ $attrs }}>
                        </td>
                        <td colspan="5" class="af-label af-bold">PIN Code</td>
                        <td colspan="10">
                            <input type="text" id="af_pincode" name="af[pincode]" value="{{ $val('pincode') }}" maxlength="6" inputmode="numeric" class="af-cell-input" placeholder="6-digit PIN" style="letter-spacing:4px;font-family:monospace;max-width:140px;" {{ $attrs }}>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="14" class="af-label af-bold">Business Premises</td>
                        <td colspan="7" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[business_premises]" value="Rented" {{ $attrs }} @checked($val('business_premises') === 'Rented')>
                                Rented
                            </label>
                        </td>
                        <td colspan="8" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[business_premises]" value="Owned" {{ $attrs }} @checked($val('business_premises') === 'Owned')>
                                Owned
                            </label>
                        </td>
                    </tr>
                    {{-- Row 14: Telephone & Mobile --}}
                    <tr>
                        <td colspan="3" class="af-label af-bold">Telephone No.</td>
                        <td colspan="8">
                            <input type="text" id="af_telephone" name="af[telephone]" value="{{ $val('telephone') }}" class="af-cell-input" placeholder="Telephone No." {{ $attrs }}>
                        </td>
                        <td colspan="6" class="af-label af-bold">Mobile No.</td>
                        <td colspan="1">
                            <input type="text" id="af_mobile_country_code" name="af[mobile_country_code]" value="{{ $val('mobile_country_code') ?? '91' }}" maxlength="4" class="af-cell-input text-center" title="Country Code" placeholder="91" {{ $attrs }}>
                        </td>
                        <td colspan="14">
                            <input type="tel" id="af_mobile_number" name="af[mobile_number]" value="{{ $val('mobile_number') }}" class="af-cell-input" placeholder="Mobile No." {{ $attrs }}>
                        </td>
                    </tr>
                    {{-- Row 15: Email --}}
                    <tr>
                        <td colspan="3" class="af-label af-bold">E-mail:</td>
                        <td colspan="29">
                            <input type="email" id="af_email" name="af[email]" value="{{ $val('email') }}" class="af-cell-input" placeholder="email@example.com" {{ $attrs }}>
                        </td>
                    </tr>
                    {{-- Rows 16 & 17: Business Activity --}}
                    <tr>
                        <td colspan="3" rowspan="2" class="af-label af-bold text-center" style="vertical-align:middle;">
                            Business<br>Activity
                        </td>
                        <td colspan="9" class="af-label af-bold">Existing</td>
                        <td colspan="20">
                            <textarea id="af_activity_existing" name="af[activity_existing]" rows="2" class="af-cell-textarea" placeholder="Existing business activity" {{ $attrs }}>{{ $val('activity_existing') }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9" class="af-label af-bold">Proposed</td>
                        <td colspan="20">
                            <textarea id="af_activity_proposed" name="af[activity_proposed]" rows="2" class="af-cell-textarea" placeholder="Proposed business activity" {{ $attrs }}>{{ $val('activity_proposed') }}</textarea>
                        </td>
                    </tr>
                    {{-- Row 18: Commencement Date --}}
                    <tr>
                        <td colspan="12" class="af-label af-bold">Date of Commencement(DD/MM/YYYY)</td>
                        <td colspan="20">
                            <input type="date" id="af_commencement_date" name="af[commencement_date]" value="{{ $val('commencement_date') }}" class="af-cell-input" style="max-width:180px;" {{ $attrs }}>
                        </td>
                    </tr>
                    {{-- Row 19: Whether Registered --}}
                    <tr>
                        <td colspan="12" class="af-label af-bold">Whether the Unit is Registered</td>
                        <td colspan="4" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[is_registered]" value="Yes" {{ $attrs }} @checked($val('is_registered') === 'Yes')>
                                Yes
                            </label>
                        </td>
                        <td colspan="8" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[is_registered]" value="No" {{ $attrs }} @checked($val('is_registered') === 'No')>
                                No
                            </label>
                        </td>
                        <td colspan="8"></td>
                    </tr>
                    {{-- Row 20: Registration No. & Act --}}
                    <tr>
                        <td colspan="12" class="af-label af-bold">
                            If Registered (Please mention: Registration no. And the Act under which registered )
                        </td>
                        <td colspan="20">
                            <input type="text" id="af_registration_no_act" name="af[registration_no_act]" value="{{ $val('registration_no_act') }}" class="af-cell-input" placeholder="Registration No. / Act" {{ $attrs }}>
                        </td>
                    </tr>
                    {{-- Row 21: Registered Office Address --}}
                    <tr>
                        <td colspan="12" class="af-label af-bold">Registered office Address</td>
                        <td colspan="20">
                            <textarea id="af_registered_office_address" name="af[registered_office_address]" rows="2" class="af-cell-textarea" placeholder="Registered office address" {{ $attrs }}>{{ $val('registered_office_address') }}</textarea>
                        </td>
                    </tr>
                    {{-- Row 22: Social Category --}}
                    <tr>
                        <td colspan="12" class="af-label af-bold">Social Category</td>
                        <td colspan="3" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[social_category]" value="General" {{ $attrs }} @checked($val('social_category') === 'General')>
                                General
                            </label>
                        </td>
                        <td colspan="2" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[social_category]" value="SC" {{ $attrs }} @checked($val('social_category') === 'SC')>
                                SC
                            </label>
                        </td>
                        <td colspan="2" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[social_category]" value="ST" {{ $attrs }} @checked($val('social_category') === 'ST')>
                                ST
                            </label>
                        </td>
                        <td colspan="4" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[social_category]" value="OBC" {{ $attrs }} @checked($val('social_category') === 'OBC')>
                                OBC
                            </label>
                        </td>
                        <td colspan="9" class="af-label af-bold text-center" style="background:#f8fafc;">Minority Community</td>
                    </tr>
                    {{-- Row 23: If Minority Community --}}
                    <tr>
                        <td colspan="4" class="af-label af-bold">If Minority Community</td>
                        <td colspan="5" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[minority_community]" value="Buddhists" {{ $attrs }} @checked($val('minority_community') === 'Buddhists')>
                                Buddhists
                            </label>
                        </td>
                        <td colspan="3" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[minority_community]" value="Muslims" {{ $attrs }} @checked($val('minority_community') === 'Muslims')>
                                Muslims
                            </label>
                        </td>
                        <td colspan="4" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[minority_community]" value="Christians" {{ $attrs }} @checked($val('minority_community') === 'Christians')>
                                Christians
                            </label>
                        </td>
                        <td colspan="3" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[minority_community]" value="Sikhs" {{ $attrs }} @checked($val('minority_community') === 'Sikhs')>
                                Sikhs
                            </label>
                        </td>
                        <td colspan="4" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[minority_community]" value="Jains" {{ $attrs }} @checked($val('minority_community') === 'Jains')>
                                Jains
                            </label>
                        </td>
                        <td colspan="9" class="af-choice-cell">
                            <label class="af-choice-label">
                                <input type="radio" name="af[minority_community]" value="Zoroastrians" {{ $attrs }} @checked($val('minority_community') === 'Zoroastrians')>
                                Zoroastrians
                            </label>
                        </td>
                    </tr>
                    {{-- Row 24: Section C --}}
                    <tr>
                        <td colspan="32" class="af-section-banner">
                            C. Background of Proprietor / Partners / Directors:
                        </td>
                    </tr>
                    {{-- Row 25: Proprietor Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="1">S.No</td>
                        <td colspan="6">Name</td>
                        <td colspan="3">Date of Birth</td>
                        <td colspan="2">Sex</td>
                        <td colspan="6">Residential Address with Mobile No.</td>
                        <td colspan="7">Academic Qualification</td>
                        <td colspan="7">Experience in the line of activity (Years)</td>
                    </tr>
                    {{-- Row 26/27: Proprietor Data --}}
                    <tr>
                        <td colspan="1" class="text-center fw-bold" style="vertical-align:middle;">1</td>
                        <td colspan="6">
                            <input type="text" id="af_applicant_name" name="af[applicant_name]" value="{{ $val('applicant_name') }}" class="af-cell-input fw-semibold" placeholder="Applicant Name" {{ $attrs }}>
                        </td>
                        <td colspan="3">
                            <input type="date" id="af_date_of_birth" name="af[date_of_birth]" value="{{ $val('date_of_birth') }}" class="af-cell-input text-center" {{ $attrs }}>
                        </td>
                        <td colspan="2">
                            <select id="af_sex" name="af[sex]" class="af-cell-select text-center" {{ $attrs }}>
                                <option value="">—</option>
                                <option value="M" @selected($val('sex') === 'M')>M (Male)</option>
                                <option value="F" @selected($val('sex') === 'F')>F (Female)</option>
                                <option value="O" @selected($val('sex') === 'O')>O (Other)</option>
                            </select>
                        </td>
                        <td colspan="6">
                            <textarea id="af_residential_address" name="af[residential_address]" rows="2" class="af-cell-textarea mb-1" placeholder="Residential Address" {{ $attrs }}>{{ $val('residential_address') }}</textarea>
                            <div class="d-flex align-items-center gap-1">
                                <span style="font-size:11px;color:#666;font-weight:bold;">Mob:</span>
                                <input type="tel" id="af_applicant_mobile" name="af[applicant_mobile]" value="{{ $val('applicant_mobile') }}" placeholder="Mobile No." class="af-cell-input" {{ $attrs }}>
                            </div>
                        </td>
                        <td colspan="7">
                            <input type="text" id="af_qualification" name="af[qualification]" value="{{ $val('qualification') }}" class="af-cell-input" placeholder="Qualification" {{ $attrs }}>
                        </td>
                        <td colspan="7">
                            <input type="text" id="af_experience_years" name="af[experience_years]" value="{{ $val('experience_years') }}" class="af-cell-input" placeholder="Years" {{ $attrs }}>
                        </td>
                    </tr>
                    {{-- Row 28: ID Proof Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="1">S.No</td>
                        <td colspan="3">Id proof</td>
                        <td colspan="4">Id proof no.</td>
                        <td colspan="3">Address proof</td>
                        <td colspan="4">Address proof no.</td>
                        <td colspan="6">PAN Card/DIN No.</td>
                        <td colspan="11">Relationship with the officials/ Director of the bank if any</td>
                    </tr>
                    {{-- Row 29: ID Proof Data --}}
                    <tr>
                        <td colspan="1" class="text-center fw-bold" style="vertical-align:middle;">1</td>
                        <td colspan="3">
                            <select id="af_id_proof_type" name="af[id_proof_type]" class="af-cell-select" {{ $attrs }}>
                                <option value="">— Select —</option>
                                @foreach (AF::ID_PROOFS as $proof)
                                    <option value="{{ $proof }}" @selected($val('id_proof_type') === $proof)>{{ $proof }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td colspan="4">
                            <input type="text" id="af_id_proof_number" name="af[id_proof_number]" value="{{ $val('id_proof_number') }}" class="af-cell-input text-center" placeholder="ID Proof No." {{ $attrs }}>
                        </td>
                        <td colspan="3">
                            <select id="af_address_proof_type" name="af[address_proof_type]" class="af-cell-select" {{ $attrs }}>
                                <option value="">— Select —</option>
                                @foreach (AF::ADDRESS_PROOFS as $proof)
                                    <option value="{{ $proof }}" @selected($val('address_proof_type') === $proof)>{{ $proof }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td colspan="4">
                            <input type="text" id="af_address_proof_number" name="af[address_proof_number]" value="{{ $val('address_proof_number') }}" class="af-cell-input text-center" placeholder="Address Proof No." {{ $attrs }}>
                        </td>
                        <td colspan="6">
                            <input type="text" id="af_pan_din_number" name="af[pan_din_number]" value="{{ $val('pan_din_number') }}" class="af-cell-input text-center fw-semibold" placeholder="PAN Card / DIN" {{ $attrs }}>
                        </td>
                        <td colspan="11">
                            <input type="text" id="af_bank_relationship" name="af[bank_relationship]" value="{{ $val('bank_relationship') }}" class="af-cell-input" placeholder="Relationship, if any" {{ $attrs }}>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== TABLE 2 ===================== --}}
    <div id="af-t2" class="af-sub-panel jb-hidden">
        <div class="af-table-wrapper">
            <table class="af-excel-table">
                <colgroup>
                    <col style="width: 9.01%">
                    <col style="width: 6.01%">
                    <col style="width: 2.38%">
                    <col style="width: 1.15%">
                    <col style="width: 1.33%">
                    <col style="width: 5.74%">
                    <col style="width: 5.12%">
                    <col style="width: 1.77%">
                    <col style="width: 4.15%">
                    <col style="width: 5.74%">
                    <col style="width: 4.24%">
                    <col style="width: 2.83%">
                    <col style="width: 5.74%">
                    <col style="width: 4.59%">
                    <col style="width: 1.06%">
                    <col style="width: 3.09%">
                    <col style="width: 2.83%">
                    <col style="width: 5.92%">
                    <col style="width: 7.07%">
                    <col style="width: 1.33%">
                    <col style="width: 6.36%">
                    <col style="width: 12.55%">
                </colgroup>
                <tbody>
                    {{-- Row 1: Section D --}}
                    <tr>
                        <td colspan="22" class="af-section-banner">
                            D. Associate Concerns and Nature of Association:
                        </td>
                    </tr>
                    {{-- Row 2: Associate Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="2">Names of Associate Concern</td>
                        <td colspan="5">Address of Associate Concern</td>
                        <td colspan="4">Presently Banking with</td>
                        <td colspan="5">Nature of Association Concern</td>
                        <td colspan="6">Extent of Interest as a Prop./Partner/ Director or Just Investor in Associate Concern</td>
                    </tr>
                </tbody>
                {{-- Rows 3 & 4: Associate Concerns Data --}}
                <tbody id="af-associates">
                    @for ($i = 0; $i < AF::MAX_ASSOCIATES; $i++)
                        @php
                            $rowFilled = collect($af['associate_concerns'][$i] ?? [])->filter()->isNotEmpty() || collect(old("af.associate_concerns.$i", []))->filter()->isNotEmpty();
                        @endphp
                        <tr class="af-repeat-row {{ $i > 0 && ! $rowFilled ? 'jb-hidden' : '' }}">
                            <td colspan="2">
                                <input type="text" name="af[associate_concerns][{{ $i }}][name]" value="{{ $val("associate_concerns.$i.name") }}" class="af-cell-input" placeholder="Associate concern name" {{ $attrs }}>
                            </td>
                            <td colspan="5">
                                <input type="text" name="af[associate_concerns][{{ $i }}][address]" value="{{ $val("associate_concerns.$i.address") }}" class="af-cell-input" placeholder="Address" {{ $attrs }}>
                            </td>
                            <td colspan="4">
                                <input type="text" name="af[associate_concerns][{{ $i }}][bank]" value="{{ $val("associate_concerns.$i.bank") }}" class="af-cell-input" placeholder="Bank name" {{ $attrs }}>
                            </td>
                            <td colspan="5">
                                <input type="text" name="af[associate_concerns][{{ $i }}][nature]" value="{{ $val("associate_concerns.$i.nature") }}" class="af-cell-input" placeholder="Nature of association" {{ $attrs }}>
                            </td>
                            <td colspan="6">
                                <input type="text" name="af[associate_concerns][{{ $i }}][interest]" value="{{ $val("associate_concerns.$i.interest") }}" class="af-cell-input" placeholder="Extent of interest" {{ $attrs }}>
                            </td>
                        </tr>
                    @endfor
                </tbody>
                @if ($canWrite)
                    <tbody>
                        <tr>
                            <td colspan="22" class="p-1 bg-light">
                                <button type="button" class="btn btn-link btn-sm p-0 af-add-row" data-target="af-associates">+ Add associate concern</button>
                            </td>
                        </tr>
                    </tbody>
                @endif
                <tbody>
                    {{-- Row 5: Section E --}}
                    <tr>
                        <td colspan="22" class="af-section-banner">
                            E. Banking / Credit Facilities Existing: (In Rs.)
                        </td>
                    </tr>
                    {{-- Row 6: Facilities Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="4">Type of Facilities</td>
                        <td colspan="5">Presently Banking with</td>
                        <td colspan="3">Limit Availed</td>
                        <td colspan="5">Outstanding As on ……</td>
                        <td colspan="2">Security lodged</td>
                        <td colspan="3">Asset classification status</td>
                    </tr>
                    {{-- Rows 7 to 11: Facilities Data --}}
                    @foreach (AF::EXISTING_FACILITIES as $key => $label)
                        <tr>
                            <td colspan="4" class="fw-semibold">{{ $label }}</td>
                            <td colspan="5">
                                <input type="text" name="af[existing_facilities][{{ $key }}][bank]" value="{{ $val("existing_facilities.$key.bank") }}" class="af-cell-input" placeholder="Bank name" {{ $attrs }}>
                            </td>
                            <td colspan="3">
                                <input type="text" name="af[existing_facilities][{{ $key }}][limit]" value="{{ $val("existing_facilities.$key.limit") }}" class="af-cell-input text-end" placeholder="Limit" {{ $attrs }}>
                            </td>
                            <td colspan="5">
                                <input type="text" name="af[existing_facilities][{{ $key }}][outstanding]" value="{{ $val("existing_facilities.$key.outstanding") }}" class="af-cell-input text-end" placeholder="Outstanding" {{ $attrs }}>
                            </td>
                            <td colspan="2">
                                <input type="text" name="af[existing_facilities][{{ $key }}][security]" value="{{ $val("existing_facilities.$key.security") }}" class="af-cell-input" placeholder="Security" {{ $attrs }}>
                            </td>
                            <td colspan="3">
                                <input type="text" name="af[existing_facilities][{{ $key }}][status]" value="{{ $val("existing_facilities.$key.status") }}" class="af-cell-input text-center" placeholder="Status" {{ $attrs }}>
                            </td>
                        </tr>
                    @endforeach
                    {{-- Row 12: Customer ID --}}
                    <tr>
                        <td colspan="17" class="af-bold">
                            If banking with this bank, customer ID to be given here:
                        </td>
                        <td colspan="5">
                            <input type="text" id="af_customer_id" name="af[customer_id]" value="{{ $val('customer_id') }}" class="af-cell-input fw-semibold" placeholder="Customer ID" {{ $attrs }}>
                        </td>
                    </tr>
                    {{-- Row 13: Certification Note --}}
                    <tr>
                        <td colspan="22" class="af-sub-banner" style="font-size:12px;padding:6px 10px;line-height:1.35;">
                            It is certified that our unit has not availed any loan from any other Bank / Financial Institution in the past and I/we am/are not indebted to any other Bank / Financial Institution other than those mentioned in column no. E above.
                        </td>
                    </tr>
                    {{-- Row 14: Section F --}}
                    <tr>
                        <td colspan="22" class="af-section-banner">
                            F. Credit Facilities Proposed: (In Rs.)
                        </td>
                    </tr>
                    {{-- Row 15: Proposed Facilities Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="5">Type of Facilities</td>
                        <td colspan="3">Amount</td>
                        <td colspan="5">Purpose for which Required</td>
                        <td colspan="9">Details of Primary Security Offered (with approx. value to be mentioned)</td>
                    </tr>
                    {{-- Rows 16 to 18: Proposed Facilities Data --}}
                    @foreach (AF::PROPOSED_FACILITIES as $key => $label)
                        <tr>
                            <td colspan="5" class="fw-semibold">{{ $label }}</td>
                            <td colspan="3">
                                <input type="number" step="0.01" min="0" data-sum="proposed" name="af[proposed_facilities][{{ $key }}][amount]" value="{{ $val("proposed_facilities.$key.amount") }}" class="af-cell-input text-end fw-semibold" placeholder="0.00" {{ $attrs }}>
                            </td>
                            <td colspan="5">
                                <input type="text" name="af[proposed_facilities][{{ $key }}][purpose]" value="{{ $val("proposed_facilities.$key.purpose") }}" class="af-cell-input" placeholder="Purpose" {{ $attrs }}>
                            </td>
                            <td colspan="9">
                                <input type="text" name="af[proposed_facilities][{{ $key }}][security]" value="{{ $val("proposed_facilities.$key.security") }}" class="af-cell-input" placeholder="Security details" {{ $attrs }}>
                            </td>
                        </tr>
                    @endforeach
                    {{-- Row 19: Proposed Total --}}
                    <tr style="background:#f8fafc;" class="fw-bold">
                        <td colspan="5">Total</td>
                        <td colspan="3">
                            <span id="af-total-proposed" class="text-end d-block fw-bold" style="padding:2px 4px;">—</span>
                        </td>
                        <td colspan="14"></td>
                    </tr>
                    {{-- Row 20: Section G --}}
                    <tr>
                        <td colspan="22" class="af-section-banner">
                            G. Working Capital — In case of Working Capital: Basis of Cash Credit Limit applied: (In Rs.)
                        </td>
                    </tr>
                    {{-- Row 21: Working Capital Group Header --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="3">Actual Sales</td>
                        <td colspan="19">Projected</td>
                    </tr>
                    {{-- Row 22: Working Capital Column Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="1">
                            <input type="text" name="af[wc_fy_label_1]" value="{{ $val('wc_fy_label_1') ?? 'FY-2024-25' }}" placeholder="FY-2024-25" class="af-cell-input fw-semibold text-center" {{ $attrs }}>
                        </td>
                        <td colspan="2">
                            <input type="text" name="af[wc_fy_label_2]" value="{{ $val('wc_fy_label_2') ?? 'FY-2025-26' }}" placeholder="FY-2025-26" class="af-cell-input fw-semibold text-center" {{ $attrs }}>
                        </td>
                        <td colspan="3">Sales</td>
                        <td colspan="3">Working Cycle in Months</td>
                        <td colspan="3">Inventory</td>
                        <td colspan="3">Debtors</td>
                        <td colspan="3">Creditors</td>
                        <td colspan="3">Promoter’s Contribution</td>
                        <td colspan="1">Limits</td>
                    </tr>
                    {{-- Row 23: Working Capital Data Row --}}
                    <tr>
                        <td colspan="1">
                            <input type="number" step="0.01" min="0" name="af[wc_actual_sales_1]" value="{{ $val('wc_actual_sales_1') }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                        </td>
                        <td colspan="2">
                            <input type="number" step="0.01" min="0" name="af[wc_actual_sales_2]" value="{{ $val('wc_actual_sales_2') }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                        </td>
                        <td colspan="3">
                            <input type="number" step="0.01" min="0" name="af[wc_projected_sales]" value="{{ $val('wc_projected_sales') }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                        </td>
                        <td colspan="3">
                            <input type="text" name="af[wc_working_cycle_months]" value="{{ $val('wc_working_cycle_months') }}" class="af-cell-input text-center" placeholder="Months" {{ $attrs }}>
                        </td>
                        <td colspan="3">
                            <input type="number" step="0.01" min="0" name="af[wc_inventory]" value="{{ $val('wc_inventory') }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                        </td>
                        <td colspan="3">
                            <input type="number" step="0.01" min="0" name="af[wc_debtors]" value="{{ $val('wc_debtors') }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                        </td>
                        <td colspan="3">
                            <input type="number" step="0.01" min="0" name="af[wc_creditors]" value="{{ $val('wc_creditors') }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                        </td>
                        <td colspan="3">
                            <input type="number" step="0.01" min="0" name="af[wc_promoter_contribution]" value="{{ $val('wc_promoter_contribution') }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                        </td>
                        <td colspan="1">
                            <input type="number" step="0.01" min="0" name="af[wc_limits]" value="{{ $val('wc_limits') }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                        </td>
                    </tr>
                    {{-- Row 24: Section H --}}
                    <tr>
                        <td colspan="22" class="af-section-banner">
                            H. Term Loan — In case of Term loan requirements, the details of machinery/equipment may be given as under:
                        </td>
                    </tr>
                    {{-- Row 25: Machinery Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="3">Type of machine / Equipment</td>
                        <td colspan="4">Purpose for which required</td>
                        <td colspan="3">Name of Supplier</td>
                        <td colspan="4">Total Cost of Machine</td>
                        <td colspan="6">Contribution being made by the promoters(Rs.)</td>
                        <td colspan="2">Loan Required (Rs.)</td>
                    </tr>
                </tbody>
                {{-- Rows 26 & 27: Machinery Data Rows --}}
                <tbody id="af-machinery">
                    @for ($i = 0; $i < AF::MAX_MACHINERY; $i++)
                        @php
                            $mFilled = collect($af['machinery'][$i] ?? [])->filter()->isNotEmpty() || collect(old("af.machinery.$i", []))->filter()->isNotEmpty();
                        @endphp
                        <tr class="af-repeat-row {{ $i > 0 && ! $mFilled ? 'jb-hidden' : '' }}">
                            <td colspan="3">
                                <input type="text" name="af[machinery][{{ $i }}][type]" value="{{ $val("machinery.$i.type") }}" class="af-cell-input" placeholder="Machine / equipment type" {{ $attrs }}>
                            </td>
                            <td colspan="4">
                                <input type="text" name="af[machinery][{{ $i }}][purpose]" value="{{ $val("machinery.$i.purpose") }}" class="af-cell-input" placeholder="Purpose" {{ $attrs }}>
                            </td>
                            <td colspan="3">
                                <input type="text" name="af[machinery][{{ $i }}][supplier]" value="{{ $val("machinery.$i.supplier") }}" class="af-cell-input" placeholder="Supplier name" {{ $attrs }}>
                            </td>
                            <td colspan="4">
                                <input type="number" step="0.01" min="0" data-sum="total_cost" name="af[machinery][{{ $i }}][total_cost]" value="{{ $val("machinery.$i.total_cost") }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                            </td>
                            <td colspan="6">
                                <input type="number" step="0.01" min="0" data-sum="promoter_contribution" name="af[machinery][{{ $i }}][promoter_contribution]" value="{{ $val("machinery.$i.promoter_contribution") }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                            </td>
                            <td colspan="2">
                                <input type="number" step="0.01" min="0" data-sum="loan_required" name="af[machinery][{{ $i }}][loan_required]" value="{{ $val("machinery.$i.loan_required") }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                            </td>
                        </tr>
                    @endfor
                </tbody>
                @if ($canWrite)
                    <tbody>
                        <tr>
                            <td colspan="22" class="p-1 bg-light">
                                <button type="button" class="btn btn-link btn-sm p-0 af-add-row" data-target="af-machinery">+ Add machinery / equipment</button>
                            </td>
                        </tr>
                    </tbody>
                @endif
                <tbody>
                    {{-- Row 28: Machinery Totals --}}
                    <tr style="background:#f8fafc;" class="fw-bold">
                        <td colspan="10" class="text-end">Total</td>
                        <td colspan="4">
                            <span id="af-total-total_cost" class="text-end d-block fw-bold" style="padding:2px 4px;">—</span>
                        </td>
                        <td colspan="6">
                            <span id="af-total-promoter_contribution" class="text-end d-block fw-bold" style="padding:2px 4px;">—</span>
                        </td>
                        <td colspan="2">
                            <span id="af-total-loan_required" class="text-end d-block fw-bold" style="padding:2px 4px;">—</span>
                        </td>
                    </tr>
                    {{-- Row 29: Repayment & Moratorium --}}
                    <tr>
                        <td colspan="14" class="af-bold">
                            Repayment period with Moratorium period requested for
                        </td>
                        <td colspan="8">
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-nowrap fw-semibold">Repayment:</span>
                                <input type="text" id="af_repayment_period" name="af[repayment_period]" value="{{ $val('repayment_period') }}" placeholder="e.g. 5 Years" class="af-cell-input flex-grow-1" {{ $attrs }}>
                                <span class="small text-nowrap fw-semibold ms-2">Moratorium:</span>
                                <input type="text" id="af_moratorium_period" name="af[moratorium_period]" value="{{ $val('moratorium_period') }}" placeholder="e.g. 6 Months" class="af-cell-input flex-grow-1" {{ $attrs }}>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== TABLE 3 ===================== --}}
    <div id="af-t3" class="af-sub-panel jb-hidden">
        <div class="af-table-wrapper">
            <table class="af-excel-table">
                <colgroup>
                    <col style="width: 18.68%">
                    <col style="width: 15.35%">
                    <col style="width: 3.07%">
                    <col style="width: 15.35%">
                    <col style="width: 3.33%">
                    <col style="width: 10.97%">
                    <col style="width: 5.70%">
                    <col style="width: 1.93%">
                    <col style="width: 25.62%">
                </colgroup>
                <tbody>
                    {{-- Row 1: Section I --}}
                    <tr>
                        <td colspan="9" class="af-section-banner">
                            I. Past Performance / Future Estimates: (In Rs.)
                        </td>
                    </tr>
                    {{-- Row 2: Sub-note --}}
                    <tr>
                        <td colspan="9" class="af-sub-banner" style="font-size:12px;padding:6px 10px;line-height:1.35;">
                            Past Performance / Future Estimates (Actual performance for two previous years, estimates for current year and projections for next year to be provided for working capital facilities. However for term loan facilities projections to be provided till the proposed year of repayment of loan)
                        </td>
                    </tr>
                    {{-- Row 3: Performance Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="1"></td>
                        <td colspan="2">Past Year-II (Actual)</td>
                        <td colspan="2">Past Year-I (Actual)</td>
                        <td colspan="3">Present Year (Estimate)</td>
                        <td colspan="1">Next Year (Projection)</td>
                    </tr>
                    {{-- Rows 4 to 6: Performance Rows --}}
                    @php
                        $perfRows = [
                            'net_sales' => 'Net Sales',
                            'net_profit' => 'Net Profit',
                            'net_worth' => 'Capital (Net Worth in case of Companies)'
                        ];
                    @endphp
                    @foreach ($perfRows as $metricKey => $metricLabel)
                        <tr>
                            <td colspan="1" class="fw-semibold">{{ $metricLabel }}</td>
                            <td colspan="2">
                                <input type="number" step="0.01" min="0" name="af[performance][past_year_2][{{ $metricKey }}]" value="{{ $val("performance.past_year_2.$metricKey") }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                            </td>
                            <td colspan="2">
                                <input type="number" step="0.01" min="0" name="af[performance][past_year_1][{{ $metricKey }}]" value="{{ $val("performance.past_year_1.$metricKey") }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                            </td>
                            <td colspan="3">
                                <input type="number" step="0.01" min="0" name="af[performance][present_year][{{ $metricKey }}]" value="{{ $val("performance.present_year.$metricKey") }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                            </td>
                            <td colspan="1">
                                <input type="number" step="0.01" min="0" name="af[performance][next_year][{{ $metricKey }}]" value="{{ $val("performance.next_year.$metricKey") }}" class="af-cell-input text-end" placeholder="0.00" {{ $attrs }}>
                            </td>
                        </tr>
                    @endforeach
                    {{-- Row 7: Section J --}}
                    <tr>
                        <td colspan="9" class="af-section-banner">
                            J. Status Regarding Statutory Obligations:
                        </td>
                    </tr>
                    {{-- Row 8: Statutory Headers --}}
                    <tr style="background:#f8fafc;" class="text-center af-bold">
                        <td colspan="4">Statutory Obligations</td>
                        <td colspan="3">
                            Whether Complied with (select Yes/No)<br>
                            <span class="fw-normal" style="font-size:11.5px;">If not applicable then select N. A.</span>
                        </td>
                        <td colspan="2">
                            Remarks (Any details in connection with the relevant obligation to be given )
                        </td>
                    </tr>
                    {{-- Rows 9 to 14: Obligations Data --}}
                    @foreach (AF::OBLIGATIONS as $key => $label)
                        <tr>
                            <td colspan="4" style="font-size:12.5px;">{{ $label }}</td>
                            <td colspan="3">
                                <div class="d-flex justify-content-center gap-3">
                                    @foreach (AF::COMPLIANCE as $opt)
                                        <label class="af-choice-label">
                                            <input type="radio" name="{{ $name("statutory.$key.status") }}" value="{{ $opt }}" {{ $attrs }} @checked($val("statutory.$key.status") === $opt)>
                                            {{ $opt }}
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                            <td colspan="2">
                                <input type="text" name="{{ $name("statutory.$key.remarks") }}" value="{{ $val("statutory.$key.remarks") }}" class="af-cell-input" placeholder="Remarks, if any" {{ $attrs }}>
                            </td>
                        </tr>
                    @endforeach
                    {{-- Row 15: Section K & Declaration --}}
                    <tr>
                        <td colspan="9" class="p-3" style="line-height:1.45;font-size:12px;background:#fbfcfd;">
                            <div class="af-bold mb-1" style="font-size:13.5px;">K. Declaration:</div>
                            <div>
                                I/We hereby certify that all information furnished by me/us is true, correct and complete. I/We have no borrowing arrangements for the unit except as indicated in the application form. There is/are no overdue / statutory due owed by me/us. I/We shall furnish all other information that may be required by Bank in connection with my/our application. The information may also be exchanged by you with any agency you may deem fit. You, your representatives or Reserve Bank of India or Mudra Ltd., or any other agency as authorised by you, may at any time, inspect/ verify my/our assets, books of accounts etc. in our factory/business premises as given above. You may take appropriate safeguards/action for recovery of bank’s dues.
                            </div>
                        </td>
                    </tr>
                    {{-- Row 16: Photo Space --}}
                    <tr>
                        <td colspan="2" class="p-2">
                            <div class="af-photo-box">Space for Photo</div>
                        </td>
                        <td colspan="4" class="p-2">
                            <div class="af-photo-box">Space for Photo</div>
                        </td>
                        <td colspan="3" class="p-2">
                            <div class="af-photo-box">Space for Photo</div>
                        </td>
                    </tr>
                    {{-- Row 17: Signature Line --}}
                    <tr>
                        <td colspan="9" class="text-center af-bold" style="padding:16px 8px;font-size:12.5px;">
                            (Signatures of Proprietor/partner/ director whose photo is affixed above)
                        </td>
                    </tr>
                    {{-- Rows 18 & 19: Date & Place --}}
                    <tr>
                        <td colspan="9" style="padding:6px 12px;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="af-bold" style="font-size:13.5px;">Date:</span>
                                <input type="date" id="af_declaration_date" name="af[declaration_date]" value="{{ $val('declaration_date') }}" class="af-cell-input" style="max-width:180px;" {{ $attrs }}>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="9" style="padding:6px 12px;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="af-bold" style="font-size:13.5px;">Place:</span>
                                <input type="text" id="af_declaration_place" name="af[declaration_place]" value="{{ $val('declaration_place') }}" class="af-cell-input" style="max-width:280px;" placeholder="City / Place" {{ $attrs }}>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const panel = document.getElementById('application-form-panel');
            if (!panel) return;

            // Table 1 / 2 / 3 switcher (own classes: the lead tabs' switchTab() must not touch these).
            panel.querySelectorAll('.af-sub-btn').forEach(btn => btn.addEventListener('click', () => {
                panel.querySelectorAll('.af-sub-panel').forEach(p => p.classList.add('jb-hidden'));
                document.getElementById(btn.dataset.afTarget).classList.remove('jb-hidden');
                panel.querySelectorAll('.af-sub-btn').forEach(b => {
                    b.classList.toggle('btn-primary', b === btn);
                    b.classList.toggle('btn-outline-secondary', b !== btn);
                });
            }));

            // Bidirectional sync for Enterprise Name in Table 1 (Office use vs Business Info)
            const entA = document.getElementById('af_enterprise_name');
            const entB = document.getElementById('af_enterprise_name_b');
            if (entA && entB) {
                entA.addEventListener('input', () => { entB.value = entA.value; });
                entB.addEventListener('input', () => {
                    entA.value = entB.value;
                    entA.dispatchEvent(new Event('input', { bubbles: true }));
                });
            }

            // The template has room for a fixed number of rows; "add" reveals the next one.
            panel.querySelectorAll('.af-add-row').forEach(btn => btn.addEventListener('click', () => {
                const target = document.getElementById(btn.dataset.target);
                if (!target) return;
                const next = target.querySelector('.af-repeat-row.jb-hidden');
                if (next) next.classList.remove('jb-hidden');
                if (!target.querySelector('.af-repeat-row.jb-hidden')) btn.classList.add('jb-hidden');
            }));
            panel.querySelectorAll('.af-add-row').forEach(btn => {
                const target = document.getElementById(btn.dataset.target);
                if (target && !target.querySelector('.af-repeat-row.jb-hidden')) btn.classList.add('jb-hidden');
            });

            // Live totals (the workbook computes the same sums with its own formulas).
            const fmt = n => n.toLocaleString('en-IN', {maximumFractionDigits: 2});
            function totals() {
                const sums = {};
                panel.querySelectorAll('[data-sum]').forEach(i => {
                    const k = i.dataset.sum, v = parseFloat(i.value);
                    sums[k] = sums[k] ?? null;
                    if (!isNaN(v)) sums[k] = (sums[k] ?? 0) + v;
                });
                Object.entries(sums).forEach(([k, v]) => {
                    const cell = document.getElementById(k === 'proposed' ? 'af-total-proposed' : 'af-total-' + k);
                    if (cell) cell.textContent = v === null ? '—' : fmt(v);
                });
            }
            panel.addEventListener('input', totals);
            totals();

            // ---- Details Sheet -> Application Form live linking ------------------------------
            // Mapping comes from ApplicationFormData::LEAD_LINKS. A linked field follows its Details
            // Sheet source(s) until the user edits it by hand; after that it is theirs.
            const config = @json(\App\Support\ApplicationFormData::linkConfig($saved));
            const leadForm = panel.closest('form');
            const manual = new Set(config.overrides);
            const clean = v => (v ?? '').toString().trim();

            function sourceValue(name) {
                const el = leadForm ? leadForm.querySelector('[name="' + name + '"]') : null;
                if (!el) return '';
                if (el.tagName === 'SELECT') return el.value === '' ? '' : clean(el.options[el.selectedIndex]?.text);
                return clean(el.value);
            }

            const sexFrom = g => { g = g.toLowerCase(); return g === '' ? null : g.startsWith('m') ? 'M' : g.startsWith('f') ? 'F' : 'O'; };
            const amountFrom = v => { const s = (v ?? '').replace(/[^\d.]/g, ''); return s !== '' && !isNaN(s) ? (s.includes('.') ? s.replace(/0+$/, '').replace(/\.$/, '') : s) : null; };
            function constitutionFrom(t) {
                if (t === null) return [null, null];
                const l = t.toLowerCase();
                if (l.includes('propriet')) return ['Proprietary', null];
                if (l.includes('partner') || l.includes('llp')) return ['Partnership', null];
                if (l.includes('pvt') || l.includes('private')) return ['Pvt. Ltd.', null];
                if (l.includes('ltd') || l.includes('limited')) return ['Ltd. Company', null];
                return ['Others', t];
            }
            // Must mirror ApplicationFormData::transform().
            function transform(kind, values) {
                const first = values.find(v => v !== '') ?? null;
                switch (kind) {
                    case 'constitution': return constitutionFrom(first)[0];
                    case 'constitution_other': return constitutionFrom(first)[1];
                    case 'pincode': { const m = first !== null ? first.match(/\b(\d{6})\b/) : null; return m ? m[1] : null; }
                    case 'country_code': return first !== null ? '91' : null;
                    case 'yes_if_any': return first !== null ? 'Yes' : null;
                    case 'udyam_text': return first !== null ? 'UDYAM AADHAR [' + first + ']' : null;
                    case 'sex': return sexFrom(first ?? '');
                    case 'id_type': return values[0] ? 'PAN' : (values[1] ? 'Aadhaar Card' : null);
                    case 'aadhaar_type': return first !== null ? 'Aadhaar Card' : null;
                    case 'amount': return amountFrom(first);
                    default: return first;
                }
            }

            const fieldName = path => 'af[' + path.split('.').join('][') + ']';
            const controls = path => Array.from(panel.querySelectorAll('[name="' + fieldName(path) + '"]'));
            const currentValue = path => {
                const cs = controls(path);
                if (cs.length && cs[0].type === 'radio') return clean(cs.find(c => c.checked)?.value);
                return cs.length ? clean(cs[0].value) : '';
            };
            function setValue(path, value) {
                controls(path).forEach(c => {
                    if (c.type === 'radio') c.checked = value !== null && c.value === value;
                    else if (c.tagName === 'SELECT') c.value = value ?? '';
                    else c.value = value ?? '';
                });
                if (path === 'enterprise_name' && entB) {
                    entB.value = value ?? '';
                }
            }
            const derived = link => transform(link.transform, link.sources.map(sourceValue));

            function syncLinked() {
                config.links.forEach(link => { if (!manual.has(link.path)) setValue(link.path, derived(link)); });
            }

            // A value that differs from what the Details Sheet implies was typed by the user: keep it.
            config.links.forEach(link => {
                const now = currentValue(link.path), want = derived(link) ?? '';
                if (now !== '' && now !== want) manual.add(link.path);
            });
            syncLinked();

            const sourceNames = new Set(config.links.flatMap(l => l.sources));
            if (leadForm) {
                const onLeadChange = e => { if (!panel.contains(e.target) && sourceNames.has(e.target.name)) syncLinked(); };
                leadForm.addEventListener('input', onLeadChange);
                leadForm.addEventListener('change', onLeadChange);
            }

            // Hand edits inside the tab take a field off the link.
            const linkedByName = Object.fromEntries(config.links.map(l => [fieldName(l.path), l.path]));
            const markManual = e => { const p = linkedByName[e.target.name]; if (p) manual.add(p); };
            panel.addEventListener('input', markManual);
            panel.addEventListener('change', markManual);

            // Generation uses the saved form; warn if there are unsaved edits.
            let dirty = false;
            panel.addEventListener('input', () => dirty = true);
            const gen = document.getElementById('af-generate-btn');
            if (gen) gen.addEventListener('click', e => {
                if (dirty && !confirm('You have unsaved changes that will not be included. Generate from the last saved version?')) e.preventDefault();
            });
        });
    </script>
@endonce
