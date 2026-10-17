{{-- DPR tab panel: the per-lead values of the Detailed Project Report. Static wording and calculated totals are the
     document's own. $lead may be null (Create Lead). Controls are plain members of the lead <form> (names dpr[...]),
     so one submit saves the lead and this tab together. --}}
@php
    use App\Support\DprData as DPR;
    use Illuminate\Support\HtmlString;

    $lead = $lead ?? null;
    $exists = (bool) $lead?->exists;
    $dpr = $exists ? DPR::for($lead) : DPR::normalize([]);
    $saved = $exists ? $lead->dprReport : null;
    $canWrite = auth()->user()->can($saved?->saved_at ? 'dpr.update' : 'dpr.create');
    $hasFile = $exists && \Illuminate\Support\Facades\Storage::disk('public')->exists('dpr/' . $lead->id . '/DPR.docx');
    $dprErrors = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'dpr.'))->map(fn ($k) => $errors->first($k))->unique()->values();

    $e = fn ($v) => e((string) $v);
    $name = fn (string $path) => 'dpr[' . str_replace('.', '][', $path) . ']';
    $val = fn (string $path) => old('dpr.' . $path, data_get($dpr, $path));
    $dis = $canWrite ? '' : 'disabled';

    $control = function (string $path, string $type = 'text', string $extra = '', array $options = []) use ($name, $val, $e, $dis) {
        $id = 'dpr_' . str_replace('.', '_', $path);
        $common = 'id="' . $id . '" name="' . $name($path) . '" ' . $extra . ' ' . $dis;

        if ($type === 'select') {
            $html = '<select ' . $common . ' class="form-select form-select-sm jb-input"><option value="">— Select —</option>';
            foreach ($options as $opt) {
                $html .= '<option value="' . $e($opt) . '"' . ((string) $val($path) === (string) $opt ? ' selected' : '') . '>' . $e($opt) . '</option>';
            }

            return new HtmlString($html . '</select>');
        }
        if ($type === 'textarea') {
            return new HtmlString('<textarea ' . $common . ' rows="2" class="form-control form-control-sm jb-input">' . $e($val($path)) . '</textarea>');
        }

        return new HtmlString('<input type="' . $type . '" ' . $common . ' value="' . $e($val($path)) . '" class="form-control form-control-sm jb-input">');
    };

    $field = fn (string $path, string $label, string $type = 'text', string $col = 'col-12 col-md-4', string $extra = '', array $options = []) => new HtmlString(
        '<div class="' . $col . '"><label class="form-label mb-1" for="dpr_' . str_replace('.', '_', $path) . '" style="font-size:var(--text-xs);font-weight:600;color:var(--slate-600)">' . $e($label) . '</label>' . $control($path, $type, $extra, $options) . '</div>'
    );

    $rowFilled = fn (string $group, int $i) => collect(old("dpr.$group.$i", data_get($dpr, "$group.$i", [])))->filter()->isNotEmpty();
    $amount = 'inputmode="decimal"';

    $subTabs = [
        'basic' => 'Basic Information', 'business' => 'Business Profile', 'loans' => 'Current Loan Details', 'required' => 'Required Loan Amount',
        'cost' => 'Project Cost', 'finance' => 'Means of Finance', 'quotation' => 'Quotation Summary for New Machinery',
        'collateral' => 'Details of Collateral Property/CGTMSE', 'financials' => 'Financial Details in Brief',
    ];
    $subKeys = array_keys($subTabs);
    $panelAttrs = fn (string $key) => 'id="dpr-s-' . $key . '" class="dpr-sub-panel' . ($key === 'basic' ? '' : ' jb-hidden') . '"';
    $removeBtn = $canWrite ? '<button type="button" class="dpr-remove" title="Remove row" aria-label="Remove row">&times;</button>' : '';
    $sectionNav = function (string $key) use ($subKeys, $subTabs) {
        $i = array_search($key, $subKeys, true);
        $prev = $i > 0 ? '<button type="button" class="btn btn-outline-secondary btn-sm dpr-sub-go" data-dpr-go="' . $subKeys[$i - 1] . '">&larr; ' . e($subTabs[$subKeys[$i - 1]]) . '</button>' : '<span></span>';
        $next = $i < count($subKeys) - 1 ? '<button type="button" class="btn btn-outline-primary btn-sm dpr-sub-go" data-dpr-go="' . $subKeys[$i + 1] . '">' . e($subTabs[$subKeys[$i + 1]]) . ' &rarr;</button>' : '<span></span>';

        return new HtmlString('<div class="d-flex justify-content-between pt-3 mt-3 border-top">' . $prev . $next . '</div>');
    };

    $dprErrorKeys = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'dpr.'))->values();
@endphp

<div id="dpr-panel" class="tab-panel jb-hidden">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-2 mb-3">
        <div>
            <h3 class="jb-section-title m-0">DPR</h3>
            <p class="jb-muted mb-0" style="font-size:var(--text-xs)">Only the values that change per customer are asked for here; the report's wording is fixed and its totals calculate automatically. Fields already in the lead's other tabs fill in automatically and stay in step as you edit them. Saved together with the lead.</p>
        </div>
        @if ($exists)
            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('dpr.generate')
                    <button type="submit" form="dpr-generate" class="btn btn-outline-primary btn-sm" id="dpr-generate-btn">Generate DPR</button>
                @endcan
                @can('dpr.download')
                    @if ($hasFile)
                        <a href="{{ route('admin.leads.dpr.download', $lead) }}" class="btn btn-success btn-sm">Download DPR</a>
                    @endif
                @endcan
            </div>
        @endif
    </div>

    @if ($dprErrors->isNotEmpty())
        <div class="alert alert-danger py-2 mb-3" role="alert">
            <strong>Please correct the following:</strong>
            <ul class="mb-0">@foreach ($dprErrors as $msg)<li>{{ $msg }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3" role="tablist" id="dpr-sub-tabs">
        @foreach ($subTabs as $key => $label)
            <button type="button" class="btn btn-sm {{ $loop->first ? 'btn-primary' : 'btn-outline-secondary' }} dpr-sub-btn" data-dpr-target="{{ $key }}">{{ $loop->iteration }}. {{ $label }}</button>
        @endforeach
    </div>

    {{-- 1. Basic Information (the template's table: No | label | value) --}}
    <div {!! $panelAttrs('basic') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">BASIC INFORMATION:</div>
            <table class="dpr-t">
                <colgroup><col style="width:5.6%"><col style="width:27.4%"><col style="width:67%"></colgroup>
                <tr><td class="dpr-c">1</td><td class="dpr-l">Name of the Unit</td><td>{!! $control('firm_name', 'text') !!}</td></tr>
                <tr><td class="dpr-c">2</td><td class="dpr-l">Constitution</td><td>{!! $control('constitution', 'text') !!}</td></tr>
                <tr><td class="dpr-c">3</td><td class="dpr-l">Name of Proprietor<br>Address</td><td>{!! $control('applicant_name', 'text') !!}{!! $control('business_address', 'textarea') !!}</td></tr>
                <tr><td class="dpr-c">4</td><td class="dpr-l">Pan no.</td><td>{!! $control('pan', 'text') !!}</td></tr>
                <tr><td class="dpr-c"></td><td class="dpr-l">ADHARCARD NO.</td><td>{!! $control('aadhaar', 'text') !!}</td></tr>
                <tr><td class="dpr-c"></td><td class="dpr-l">GENDER</td><td>{!! $control('gender', 'text') !!}</td></tr>
                <tr><td class="dpr-c">5</td><td class="dpr-l">MOBILE NO.</td><td>{!! $control('mobile', 'tel') !!}</td></tr>
                <tr><td class="dpr-c">6</td><td class="dpr-l">EMAIL ID.</td><td>{!! $control('email', 'email') !!}</td></tr>
                <tr><td class="dpr-c">7</td><td class="dpr-l">EDUCATION</td><td>{!! $control('education', 'text') !!}</td></tr>
                <tr><td class="dpr-c">8</td><td class="dpr-l">MOTHER NAME</td><td>{!! $control('mother_name', 'text') !!}</td></tr>
                <tr><td class="dpr-c">9</td><td class="dpr-l">DATE OF BIRTH</td><td>{!! $control('date_of_birth', 'date') !!}</td></tr>
                <tr><td class="dpr-c">10</td><td class="dpr-l">EXPIRIANCE</td><td>{!! $control('experience', 'text') !!}</td></tr>
                <tr><td class="dpr-c">11</td><td class="dpr-l">HISTRY /PROFILE OF PROPRIETOR</td><td>{!! $control('proprietor_history', 'textarea') !!}</td></tr>
            </table>
        </div>
        {!! $sectionNav('basic') !!}
    </div>

    {{-- 2. Business Profile --}}
    <div {!! $panelAttrs('business') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">BUSINESS PROFILE:</div>
            <table class="dpr-t">
                <colgroup><col style="width:5.6%"><col style="width:27.4%"><col style="width:67%"></colgroup>
                <tr><td class="dpr-c">1</td><td class="dpr-l">Nature of the Business Activity</td><td>{!! $control('activity', 'textarea') !!}</td></tr>
                <tr><td class="dpr-c">2</td><td class="dpr-l">BUSINESS PROFILE</td><td>{!! $control('business_profile', 'textarea') !!}</td></tr>
                <tr><td class="dpr-c">3</td><td class="dpr-l">Land and Factory Building</td><td>{!! $control('land_and_factory', 'textarea') !!}</td></tr>
                <tr><td class="dpr-c">4</td><td class="dpr-l">UDYAM NO.</td><td>{!! $control('udyam', 'text') !!}</td></tr>
                <tr><td class="dpr-c">5</td><td class="dpr-l">GST NO.</td><td>{!! $control('gst', 'text') !!}</td></tr>
                <tr><td class="dpr-c">6</td><td class="dpr-l">FSSAI LICENSE</td><td>{!! $control('fssai', 'text') !!}</td></tr>
                <tr><td class="dpr-c">7</td><td class="dpr-l">NUMBER OF MANPOWER</td><td>{!! $control('manpower', 'text') !!}</td></tr>
                <tr><td class="dpr-c">8</td><td class="dpr-l">CONNECTIVITY</td><td>{!! $control('connectivity', 'text') !!}</td></tr>
                <tr><td class="dpr-c">9</td><td class="dpr-l">ITR Filling</td><td>{!! $control('itr_filing', 'text') !!}</td></tr>
                <tr><td class="dpr-c">10</td><td class="dpr-l">Present Banking</td><td><div class="dpr-pair">{!! $control('present_bank', 'text') !!}<span>–</span>{!! $control('account_type', 'text') !!}</div><div class="dpr-pair"><span>A/C NO. –</span>{!! $control('account_number', 'text') !!}</div><div class="dpr-pair"><span>IFSC COAD :</span>{!! $control('ifsc', 'text') !!}</div></td></tr>
                <tr><td class="dpr-c"></td><td class="dpr-l">Location (cover page)</td><td>{!! $control('location', 'textarea') !!}</td></tr>
            </table>
        </div>
        {!! $sectionNav('business') !!}
    </div>

    {{-- 3. Current Loan Details --}}
    <div {!! $panelAttrs('loans') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">CURRENT LOAN DETAILS :</div>
            <table class="dpr-t">
                <colgroup><col style="width:8%"><col style="width:22.8%"><col style="width:12%"><col style="width:17.1%"><col style="width:13.7%"><col style="width:26.4%"><col class="dpr-xcol"></colgroup>
                <thead><tr><th>SR NO.</th><th>BANK NAME</th><th>LOAN TYPE</th><th>LOAN AMOUNT</th><th>EMI</th><th>OUTSTANDING AMOUNT</th><th class="dpr-x"></th></tr></thead>
                <tbody id="dpr-loans">
                    @for ($i = 0; $i < DPR::MAX_LOANS; $i++)
                        <tr class="dpr-repeat {{ $i > 0 && ! $rowFilled('current_loans', $i) ? 'jb-hidden' : '' }}">
                            <td class="dpr-c dpr-sr">{{ $i + 1 }}</td>
                            @foreach (DPR::LOAN_KEYS as $k)
                                <td>{!! $control("current_loans.$i.$k", 'text', 'data-loan-field="' . $k . '" data-loan-row="' . $i . '"') !!}</td>
                            @endforeach
                            <td class="dpr-x">{!! $removeBtn !!}</td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot><tr class="dpr-static"><td></td><td class="dpr-l">TOTAL</td><td class="dpr-c">--------</td><td class="dpr-c">-------------</td><td class="dpr-c">---------</td><td class="dpr-c">----------------------</td><td class="dpr-x"></td></tr></tfoot>
            </table>
            @if ($canWrite)<button type="button" class="btn btn-link btn-sm p-0 dpr-add" data-target="dpr-loans">+ Add Loan</button>@endif
        </div>
        {!! $sectionNav('loans') !!}
    </div>

    {{-- 4. Required Loan Amount --}}
    <div {!! $panelAttrs('required') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">REQUIRED LOAN AMOUNT :</div>
            <table class="dpr-t">
                <colgroup><col style="width:25%"><col style="width:25%"><col style="width:25%"><col style="width:25%"></colgroup>
                <thead><tr><th>LOAN TYPE</th><th>REQUIRED LOAN AMOUNT</th><th>PRO. CONTRIBUTION</th><th>TOTAL AMOUNT</th></tr></thead>
                <tbody>
                    <tr><td class="dpr-l">TERM LOAN</td><td>{!! $control('term_required', 'text', $amount . ' data-sum-row="term"') !!}</td><td>{!! $control('term_contribution', 'text', $amount . ' data-sum-row="term"') !!}</td><td class="dpr-calc" id="dpr-total-term">—</td></tr>
                    <tr><td class="dpr-l">CASH CREDIT</td><td>{!! $control('cc_required', 'text', $amount . ' data-sum-row="cc"') !!}</td><td>{!! $control('cc_contribution', 'text', $amount . ' data-sum-row="cc"') !!}</td><td class="dpr-calc" id="dpr-total-cc">—</td></tr>
                </tbody>
            </table>
        </div>
        {!! $sectionNav('required') !!}
    </div>

    {{-- 5. Project Cost --}}
    <div {!! $panelAttrs('cost') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">PROJECT COST :</div>
            <table class="dpr-t">
                <colgroup><col style="width:51%"><col style="width:49%"></colgroup>
                <tr><td class="dpr-l">LAND VALUE</td><td>{!! $control('cost_land', 'text', $amount . ' data-sum-group="cost"') !!}</td></tr>
                <tr><td class="dpr-l">BUIDING &amp; CONSTRUCTION</td><td>{!! $control('cost_building', 'text', $amount . ' data-sum-group="cost"') !!}</td></tr>
                <tr><td class="dpr-l">PLANT &amp; MACHINARY</td><td>{!! $control('cost_machinery', 'text', $amount . ' data-sum-group="cost"') !!}</td></tr>
                <tr><td class="dpr-l">WORKING CAPITAL</td><td>{!! $control('cost_working_capital', 'text', $amount . ' data-sum-group="cost"') !!}</td></tr>
                <tr><td class="dpr-l">TOTAL PROJECT COST</td><td class="dpr-calc" id="dpr-total-cost">—</td></tr>
            </table>
        </div>
        {!! $sectionNav('cost') !!}
    </div>

    {{-- 6. Means of Finance --}}
    <div {!! $panelAttrs('finance') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">MEANS OF FINANCE ;</div>
            <table class="dpr-t">
                <colgroup><col style="width:51%"><col style="width:49%"></colgroup>
                <tr><td class="dpr-l">PRAMOTER CAPITAL</td><td>{!! $control('fin_promoter_capital', 'text', $amount . ' data-sum-group="finance"') !!}</td></tr>
                <tr><td class="dpr-l">TERM LOAN</td><td>{!! $control('fin_term_loan', 'text', $amount . ' data-sum-group="finance"') !!}</td></tr>
                <tr><td class="dpr-l">CASH CREDIT</td><td>{!! $control('fin_cash_credit', 'text', $amount . ' data-sum-group="finance"') !!}</td></tr>
                <tr><td class="dpr-l">TOTAL</td><td class="dpr-calc" id="dpr-total-finance">—</td></tr>
            </table>
        </div>
        {!! $sectionNav('finance') !!}
    </div>

    {{-- 7. Quotation Summary for New Machinery --}}
    <div {!! $panelAttrs('quotation') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">QUATATION SUMMARY FOR NEW MACHINARY</div>
            <table class="dpr-t">
                <colgroup><col style="width:12.3%"><col style="width:44.5%"><col style="width:13.3%"><col style="width:29.9%"><col class="dpr-xcol"></colgroup>
                <thead><tr><th>Sr. No</th><th>Particulars</th><th>Qty</th><th>Amount (Rs.)</th><th class="dpr-x"></th></tr></thead>
                <tbody id="dpr-machinery">
                    @for ($i = 0; $i < DPR::MAX_MACHINERY; $i++)
                        <tr class="dpr-repeat {{ $i > 0 && ! $rowFilled('machinery', $i) ? 'jb-hidden' : '' }}">
                            <td class="dpr-c dpr-sr" data-suffix=".">{{ $i + 1 }}.</td>
                            <td>{!! $control("machinery.$i.particulars", 'textarea') !!}</td>
                            <td>{!! $control("machinery.$i.qty") !!}</td>
                            <td>{!! $control("machinery.$i.amount", 'text', $amount . ' data-sum-group="machinery"') !!}</td>
                            <td class="dpr-x">{!! $removeBtn !!}</td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr><td></td><td class="dpr-l">TOTAL</td><td></td><td class="dpr-calc" id="dpr-total-machinery">—</td><td class="dpr-x"></td></tr>
                    <tr><td></td><td class="dpr-l">Term Loan Required</td><td></td><td class="dpr-calc" id="dpr-term-mirror">—</td><td class="dpr-x"></td></tr>
                </tfoot>
            </table>
            @if ($canWrite)<button type="button" class="btn btn-link btn-sm p-0 dpr-add" data-target="dpr-machinery">+ Add Machinery / Quotation</button>@endif
        </div>
        {!! $sectionNav('quotation') !!}
    </div>

    {{-- 8. Details of Collateral Property/CGTMSE --}}
    <div {!! $panelAttrs('collateral') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">Details of Collateral Property/CGTMSE</div>
            <table class="dpr-t">
                <colgroup><col style="width:9.5%"><col style="width:23.1%"><col style="width:42.5%"><col style="width:24.9%"><col class="dpr-xcol"></colgroup>
                <thead><tr><th>SR NO.</th><th>TYPE OF PROPERTY</th><th>ADDRESS</th><th>APROX VALUE</th><th class="dpr-x"></th></tr></thead>
                <tbody id="dpr-collateral">
                    @for ($i = 0; $i < DPR::MAX_COLLATERAL; $i++)
                        <tr class="dpr-repeat {{ $i > 0 && ! $rowFilled('collateral', $i) ? 'jb-hidden' : '' }}">
                            <td class="dpr-c dpr-sr">{{ $i + 1 }}</td>
                            <td>{!! $control("collateral.$i.type", 'select', '', DPR::PROPERTY_TYPES) !!}</td>
                            <td>{!! $control("collateral.$i.address", 'textarea') !!}</td>
                            <td>{!! $control("collateral.$i.value", 'text', $amount) !!}</td>
                            <td class="dpr-x">{!! $removeBtn !!}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
            @if ($canWrite)<button type="button" class="btn btn-link btn-sm p-0 dpr-add" data-target="dpr-collateral">+ Add Property</button>@endif
        </div>
        {!! $sectionNav('collateral') !!}
    </div>

    {{-- 9. Financial Details in Brief --}}
    <div {!! $panelAttrs('financials') !!}>
        <div class="dpr-doc">
            <div class="dpr-h">Financial Details in Brief :</div>
            <table class="dpr-t">
                <colgroup><col style="width:34%"><col style="width:22%"><col style="width:22%"><col style="width:22%"></colgroup>
                <thead><tr><th></th>@foreach (DPR::FINANCIAL_PERIODS as $label)<th>{{ $label }}<br>(Rs. in Lacs)</th>@endforeach</tr></thead>
                <tbody>
                    @foreach (DPR::FINANCIAL_ROWS as $row => $label)
                        <tr>
                            <td class="dpr-l">{{ $label }}</td>
                            @foreach (array_keys(DPR::FINANCIAL_PERIODS) as $period)
                                <td>{!! $control("financials.$row.$period", 'text', in_array($row, ['own_capital', 'unsecured_loan'], true) ? 'data-fin-part="' . $period . '"' : ($row === 'capital' ? 'data-fin-total="' . $period . '"' : '')) !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {!! $sectionNav('financials') !!}
    </div>

    <div class="d-flex justify-content-between pt-3 mt-4 border-top">
        @can('cma.view')
            <button type="button" onclick="switchTab('cma-tab', 'cma-panel')" class="btn btn-secondary jb-btn-tall">Previous: CMA</button>
        @elsecan('or.view')
            <button type="button" onclick="switchTab('opinion-report-tab', 'opinion-report-panel')" class="btn btn-secondary jb-btn-tall">Previous: Opinion Report</button>
        @else
            <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" class="btn btn-secondary jb-btn-tall">Previous: Documents</button>
        @endcan
        <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="btn btn-secondary jb-btn-tall">Next: Bank Details</button>
    </div>
</div>

@once
    <style>
        /* The DPR tables only: everything is scoped to .dpr-doc inside the DPR panel. */
        #dpr-panel .dpr-doc { overflow-x: auto; margin-bottom: .25rem; }
        #dpr-panel .dpr-doc .dpr-h { font-weight: 700; font-size: 1rem; color: #000; margin: 0 0 .35rem; }
        #dpr-panel .dpr-doc .dpr-t { width: 100%; min-width: 640px; border-collapse: collapse; table-layout: fixed; font-size: .9rem; color: #000; background: #fff; margin: 0 0 .35rem; }
        #dpr-panel .dpr-doc .dpr-t th, #dpr-panel .dpr-doc .dpr-t td { border: 1px solid #000; padding: 0; vertical-align: middle; background: #fff; }
        #dpr-panel .dpr-doc .dpr-t th { font-weight: 700; text-align: center; padding: 3px 4px; }
        #dpr-panel .dpr-doc .dpr-t td.dpr-l { font-weight: 700; padding: 3px 6px; }
        #dpr-panel .dpr-doc .dpr-t td.dpr-c { text-align: center; padding: 3px 4px; }
        #dpr-panel .dpr-doc .dpr-t td.dpr-calc { font-weight: 700; padding: 3px 6px; }
        #dpr-panel .dpr-doc .dpr-t tr.dpr-static td { font-weight: 700; }
        #dpr-panel .dpr-doc .dpr-t th.dpr-x, #dpr-panel .dpr-doc .dpr-t td.dpr-x { width: 2rem; border: 0; background: transparent; text-align: center; padding: 0 0 0 4px; }
        #dpr-panel .dpr-doc .dpr-t col.dpr-xcol { width: 2rem; }
        #dpr-panel .dpr-doc .dpr-t .form-control, #dpr-panel .dpr-doc .dpr-t .form-select {
            display: block; width: 100%; border: 0; border-radius: 0; box-shadow: none; background-color: transparent;
            padding: 3px 6px; font: inherit; color: inherit; min-height: 0; height: auto; line-height: 1.3;
        }
        #dpr-panel .dpr-doc .dpr-t textarea.form-control { resize: vertical; min-height: 2.1em; }
        #dpr-panel .dpr-doc .dpr-t .form-select { padding-right: 1.5rem; }
        #dpr-panel .dpr-doc .dpr-t .form-control:focus, #dpr-panel .dpr-doc .dpr-t .form-select:focus { background-color: #fffbe6; outline: 1px solid #0d6efd; outline-offset: -1px; box-shadow: none; }
        #dpr-panel .dpr-doc .dpr-t .form-control:disabled, #dpr-panel .dpr-doc .dpr-t .form-select:disabled { background-color: #f3f3f3; }
        #dpr-panel .dpr-doc .dpr-pair { display: flex; align-items: center; gap: 4px; padding-left: 6px; font-weight: 700; white-space: nowrap; }
        #dpr-panel .dpr-doc .dpr-pair .form-control { flex: 1 1 auto; min-width: 0; }
        #dpr-panel .dpr-doc .dpr-remove { border: 0; background: transparent; color: #b02a37; font-size: 1.1rem; line-height: 1; padding: 0 2px; cursor: pointer; }
    </style>
@endonce

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const panel = document.getElementById('dpr-panel');
            if (!panel) return;

            // ---- sub-tabs ----
            const openSub = key => {
                panel.querySelectorAll('.dpr-sub-panel').forEach(p => p.classList.toggle('jb-hidden', p.id !== 'dpr-s-' + key));
                panel.querySelectorAll('.dpr-sub-btn').forEach(b => {
                    const on = b.dataset.dprTarget === key;
                    b.classList.toggle('btn-primary', on);
                    b.classList.toggle('btn-outline-secondary', !on && !b.classList.contains('btn-outline-danger'));
                });
            };
            panel.querySelectorAll('.dpr-sub-btn').forEach(b => b.addEventListener('click', () => openSub(b.dataset.dprTarget)));
            panel.querySelectorAll('.dpr-sub-go').forEach(b => b.addEventListener('click', () => { openSub(b.dataset.dprGo); panel.scrollIntoView({block: 'start'}); }));

            // Sections holding a field the server rejected are flagged, and the first one is opened.
            let firstBad = null;
            @json($dprErrorKeys).forEach(k => {
                const name = 'dpr[' + k.slice(4).split('.').join('][') + ']';
                const sub = panel.querySelector('[name="' + name + '"]')?.closest('.dpr-sub-panel');
                if (!sub) return;
                const key = sub.id.replace('dpr-s-', '');
                const btn = panel.querySelector('.dpr-sub-btn[data-dpr-target="' + key + '"]');
                if (btn) { btn.classList.remove('btn-outline-secondary'); btn.classList.add('btn-outline-danger'); }
                firstBad = firstBad ?? key;
            });
            if (firstBad) openSub(firstBad);

            // ---- repeatable rows: add reveals the next spare row, remove clears and hides one ----
            const rowsOf = list => Array.from(list.querySelectorAll('.dpr-repeat'));
            const visibleRows = list => rowsOf(list).filter(r => !r.classList.contains('jb-hidden'));
            const renumber = list => visibleRows(list).forEach((r, i) => { const sr = r.querySelector('.dpr-sr'); if (sr) sr.textContent = (i + 1) + (sr.dataset.suffix || ''); });
            const refreshAdd = list => {
                const btn = panel.querySelector('.dpr-add[data-target="' + list.id + '"]');
                if (btn) btn.classList.toggle('jb-hidden', !rowsOf(list).some(r => r.classList.contains('jb-hidden')));
            };
            panel.querySelectorAll('.dpr-add').forEach(btn => {
                const list = document.getElementById(btn.dataset.target);
                refreshAdd(list);
                renumber(list);
                btn.addEventListener('click', () => {
                    const next = rowsOf(list).find(r => r.classList.contains('jb-hidden'));
                    if (next) next.classList.remove('jb-hidden');
                    renumber(list);
                    refreshAdd(list);
                });
            });
            panel.querySelectorAll('.dpr-remove').forEach(btn => btn.addEventListener('click', () => {
                const row = btn.closest('.dpr-repeat');
                const list = row.parentNode;
                row.querySelectorAll('input, textarea, select').forEach(el => { el.value = ''; });
                if (visibleRows(list).length > 1) {
                    row.classList.add('jb-hidden');
                    list.appendChild(row); // keep the spare slots at the end so the saved order is the shown order
                }
                renumber(list);
                refreshAdd(list);
                if (list.id === 'dpr-loans') manual.add('current_loans');
                dirty = true;
                totals();
            }));

            // ---- calculated totals (the document calculates the same sums) ----
            const num = v => { const n = parseFloat(String(v ?? '').replace(/[^\d.]/g, '')); return isNaN(n) ? null : n; };
            const fmt = n => '₹' + n.toLocaleString('en-IN', {maximumFractionDigits: 2});
            const sum = els => { let t = null; els.forEach(el => { const n = num(el.value); if (n !== null) t = (t ?? 0) + n; }); return t; };
            const show = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value === null ? '—' : fmt(value); };
            const control = path => panel.querySelector('[name="dpr[' + path + ']"]');
            const clean = v => (v ?? '').toString().trim();
            const money = path => { const n = num(control(path)?.value); return n === null ? clean(control(path)?.value) : fmt(n); };
            function totals() {
                ['term', 'cc'].forEach(k => show('dpr-total-' + k, sum(panel.querySelectorAll('[data-sum-row="' + k + '"]'))));
                ['cost', 'finance', 'machinery'].forEach(k => show('dpr-total-' + k, sum(panel.querySelectorAll('[data-sum-group="' + k + '"]'))));
                const mirror = document.getElementById('dpr-term-mirror');
                if (mirror) mirror.textContent = money('term_required') || '—';

            }
            panel.addEventListener('input', e => {
                totals();
                // Total capital follows its two parts as they are typed.
                const part = e.target.dataset?.finPart;
                if (part) {
                    const parts = panel.querySelectorAll('[data-fin-part="' + part + '"]');
                    const t = sum(parts);
                    const total = panel.querySelector('[data-fin-total="' + part + '"]');
                    if (total && t !== null) total.value = String(Math.round(t * 100) / 100);
                }
            });

            // ---- other tabs -> DPR live linking (mapping: DprData::LEAD_LINKS) ----
            const config = @json(\App\Support\DprData::linkConfig($saved));
            const leadForm = panel.closest('form');
            const manual = new Set(config.overrides);
            let dirty = false;

            function sourceValue(name) {
                const el = leadForm ? leadForm.querySelector('[name="' + name + '"]') : null;
                if (!el) return '';
                if (el.tagName === 'SELECT') return el.value === '' ? '' : clean(el.options[el.selectedIndex]?.text);
                return clean(el.value);
            }
            const derived = link => link.sources.map(sourceValue).find(v => v !== '') ?? '';

            config.links.forEach(link => {
                const c = control(link.path);
                if (c && clean(c.value) !== '' && clean(c.value) !== derived(link)) manual.add(link.path);
            });

            // The lead's Current Loans rows are mirrored row by row until a loan is edited here.
            const loanInputs = () => Array.from(panel.querySelectorAll('[data-loan-field]'));
            const leadLoan = (i, k) => sourceValue('current_loans[' + i + '][' + k + ']');
            const leadLoans = () => {
                const rows = [];
                for (let i = 0; i < loanInputs().length / config.loanKeys.length; i++) rows.push(config.loanKeys.map(k => leadLoan(i, k)));
                return rows;
            };
            const loansMatchLead = () => leadLoans().every((row, i) => config.loanKeys.every((k, j) => clean(panel.querySelector('[name="dpr[current_loans][' + i + '][' + k + ']"]')?.value) === row[j]));
            if (!loansMatchLead() && loanInputs().some(el => clean(el.value) !== '')) manual.add('current_loans');

            function syncLoans() {
                if (manual.has('current_loans')) return;
                const rows = leadLoans();
                rows.forEach((row, i) => {
                    config.loanKeys.forEach((k, j) => {
                        const el = panel.querySelector('[name="dpr[current_loans][' + i + '][' + k + ']"]');
                        if (el) el.value = row[j];
                    });
                    if (row.some(v => v !== '')) {
                        const tr = panel.querySelector('[name="dpr[current_loans][' + i + '][bank_name]"]')?.closest('tr');
                        if (tr) tr.classList.remove('jb-hidden');
                    }
                });
                const list = document.getElementById('dpr-loans');
                renumber(list);
                refreshAdd(list);
            }

            const sync = () => {
                config.links.forEach(link => {
                    const c = control(link.path);
                    if (c && !manual.has(link.path)) c.value = derived(link);
                });
                syncLoans();
                totals();
            };
            sync();

            const sourceNames = new Set(config.links.flatMap(l => l.sources));
            if (leadForm) {
                const onLead = e => {
                    if (panel.contains(e.target)) return;
                    if (sourceNames.has(e.target.name) || /^current_loans\[/.test(e.target.name)) sync();
                };
                leadForm.addEventListener('input', onLead);
                leadForm.addEventListener('change', onLead);
            }
            const byName = Object.fromEntries(config.links.map(l => ['dpr[' + l.path + ']', l.path]));
            const mark = e => {
                const p = byName[e.target.name];
                if (p) manual.add(p);
                if (/^dpr\[current_loans\]/.test(e.target.name)) manual.add('current_loans');
            };
            panel.addEventListener('input', mark);
            panel.addEventListener('change', mark);

            // Generation uses the saved DPR; warn about unsaved edits.
            panel.addEventListener('input', () => dirty = true);
            const gen = document.getElementById('dpr-generate-btn');
            if (gen) gen.addEventListener('click', e => {
                if (dirty && !confirm('You have unsaved changes that will not be included. Generate from the last saved version?')) e.preventDefault();
            });
        });
    </script>
@endonce
