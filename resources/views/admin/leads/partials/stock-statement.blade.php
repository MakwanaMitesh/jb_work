{{-- Stock Statement tab panel: an editable version of the SBI Stock Statement Excel sheet's own layout
     (grouped columns, borders, row order) for exactly the template's blank, non-formula cells. Calculated
     cells (values, closing stock, totals, drawing power) are never collected. $lead may be null (Create
     Lead). Controls are plain members of the lead <form> (names ss[...]), so one submit saves the lead and
     this tab together. --}}
@php
    use App\Support\StockStatementData as SS;
    use Illuminate\Support\HtmlString;

    $lead = $lead ?? null;
    $exists = (bool) $lead?->exists;
    $ss = $exists ? SS::for($lead) : SS::normalize([]);
    $saved = $exists ? $lead->stockStatement : null;
    $canWrite = auth()->user()->can($saved?->saved_at ? 'sbi_stock_statement.update' : 'sbi_stock_statement.create');
    $generatedPath = $exists ? 'sbi-stock-statement/' . $lead->id . '/SBI_Stock_Statement.xlsx' : null;
    $hasFile = $exists && \Illuminate\Support\Facades\Storage::disk('public')->exists($generatedPath);
    $ssErrors = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'ss.'))->map(fn ($k) => $errors->first($k))->unique()->values();

    $e = fn ($v) => e((string) $v);
    $name = fn (string $path) => 'ss[' . str_replace('.', '][', $path) . ']';
    $val = fn (string $path) => old('ss.' . $path, data_get($ss, $path));
    $dis = $canWrite ? '' : 'disabled';

    // Bare input/select for a table cell — no label, so it can sit inside the sheet's own grid.
    $cell = function (string $path, string $type = 'text', string $extra = '') use ($name, $val, $e, $dis) {
        $id = 'ss_' . str_replace('.', '_', $path);
        $common = 'id="' . $id . '" name="' . $name($path) . '" ' . $extra . ' ' . $dis;
        if ($type === 'textarea') {
            return new HtmlString('<textarea ' . $common . ' rows="1" class="form-control form-control-sm ss-in">' . $e($val($path)) . '</textarea>');
        }

        return new HtmlString('<input type="' . $type . '" ' . $common . ' value="' . $e($val($path)) . '" class="form-control form-control-sm ss-in">');
    };

    $removeBtn = $canWrite ? '<button type="button" class="ss-remove" title="Remove row" aria-label="Remove row">&times;</button>' : '';

    $itemColumns = [
        'name' => 'text', 'opening_qty' => 'number', 'opening_rate' => 'number', 'in_qty' => 'number',
        'in_rate' => 'number', 'out_qty' => 'number', 'out_rate' => 'number', 'qty_in_transit' => 'text',
    ];
@endphp

<div id="stock-statement-panel" class="tab-panel jb-hidden">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-2 mb-3">
        <div>
            <h3 class="jb-section-title m-0">Stock Statement</h3>
            <p class="jb-muted mb-0" style="font-size:var(--text-xs)">Laid out like the bank's own Annexure-FSM-3 sheet. Only the values typed into the Excel are asked for here; values, closing stock, totals and drawing power calculate in the workbook. Saved together with the lead.</p>
        </div>
        @if ($exists)
            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('sbi_stock_statement.generate')
                    <button type="submit" form="stock-statement-generate" class="btn btn-outline-primary btn-sm" id="ss-generate-btn">Generate Stock Statement</button>
                @endcan
                @can('sbi_stock_statement.download')
                    @if ($hasFile)
                        <a href="{{ route('admin.leads.sbi-stock-statement.download', $lead) }}" class="btn btn-success btn-sm">Download Stock Statement</a>
                    @endif
                @endcan
            </div>
        @endif
    </div>

    @if ($ssErrors->isNotEmpty())
        <div class="alert alert-danger py-2 mb-3" role="alert">
            <strong>Please correct the following:</strong>
            <ul class="mb-0">@foreach ($ssErrors as $msg)<li>{{ $msg }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="ss-doc">
        {{-- Header, matching the sheet's own top rows --}}
        <table class="ss-t ss-t--header">
            <colgroup><col style="width:19%"><col style="width:81%"></colgroup>
            <tr><td class="ss-hr">Annexure-FSM-3</td><td class="ss-c ss-strong">STATEMENT OF STOCK AND BOOK DEBTS AS ON {!! $cell('statement_date', 'date') !!}</td></tr>
        </table>
        <table class="ss-t ss-t--header">
            <colgroup><col style="width:30%"><col style="width:70%"></colgroup>
            <tr><td class="ss-strong">Name of the Company/unit :-</td><td>{!! $cell('company_name') !!}</td></tr>
        </table>

        {{-- Part A: INVENTORIES --}}
        <div class="ss-h">Part A: INVENTORIES <span class="ss-hint">(Rs. {!! $cell('cc_limit', 'text', 'data-ss-hint') !!} .CR/-)</span></div>
        <table class="ss-t">
            <colgroup>
                <col style="width:16%"><col style="width:7%"><col style="width:7%"><col style="width:9%"><col style="width:7%"><col style="width:7%"><col style="width:9%">
                <col style="width:7%"><col style="width:7%"><col style="width:9%"><col style="width:7%"><col style="width:5%"><col style="width:6%"><col style="width:9%">
            </colgroup>
            <thead>
                <tr><th rowspan="2"></th><th colspan="3">Opening stock</th><th colspan="3">Stocks in</th><th colspan="3">Stocks out</th><th colspan="4">Closing stock</th></tr>
                <tr><th>Quantity</th><th>Unit rate</th><th>Value</th><th>Quantity</th><th>Unit rate</th><th>Value</th><th>Quantity</th><th>Unit rate</th><th>Value</th><th>Qty in store</th><th>Qty in transit</th><th>Unit rate</th><th>Value</th></tr>
            </thead>
            <tbody>
                <tr><td class="ss-l">I- Raw materials</td><td colspan="13"></td></tr>
                <tr><td class="ss-l">'A' items* (specify whether imported or indigenous)</td><td colspan="13"></td></tr>
                <tr><td class="ss-l">… Others</td><td colspan="13"></td></tr>
                <tr><td class="ss-l">II- Stock in process</td><td colspan="13"></td></tr>
                <tr><td class="ss-l">III- Finished goods -</td><td colspan="13"></td></tr>
                <tr><td class="ss-l">'A' items*</td><td colspan="13"></td></tr>
                <tbody id="ss-items" style="display:contents">
                    @for ($i = 0; $i < SS::MAX_ITEMS; $i++)
                        @php $filled = collect($ss['items'][$i] ?? [])->filter()->isNotEmpty() || collect(old("ss.items.$i", []))->filter()->isNotEmpty(); @endphp
                        <tr class="ss-repeat-row {{ $i > 0 && ! $filled ? 'jb-hidden' : '' }}">
                            <td>{!! $cell("items.$i.name") !!}</td>
                            <td>{!! $cell("items.$i.opening_qty", 'number', 'min="0" step="any"') !!}</td>
                            <td>{!! $cell("items.$i.opening_rate", 'number', 'min="0" step="any"') !!}</td>
                            <td class="ss-calc">—</td>
                            <td>{!! $cell("items.$i.in_qty", 'number', 'min="0" step="any"') !!}</td>
                            <td>{!! $cell("items.$i.in_rate", 'number', 'min="0" step="any"') !!}</td>
                            <td class="ss-calc">—</td>
                            <td>{!! $cell("items.$i.out_qty", 'number', 'min="0" step="any"') !!}</td>
                            <td>{!! $cell("items.$i.out_rate", 'number', 'min="0" step="any"') !!}</td>
                            <td class="ss-calc">—</td>
                            <td class="ss-calc">—</td>
                            <td>{!! $cell("items.$i.qty_in_transit") !!}</td>
                            <td class="ss-calc">—</td>
                            <td class="ss-calc">—</td>
                        </tr>
                    @endfor
                </tbody>
                <tr><td class="ss-l">IV- Stores - 'A' items*</td><td colspan="13"></td></tr>
                <tr><td class="ss-l">… Others</td><td colspan="13"></td></tr>
                <tr><td class="ss-l">V- Spares - 'A' items*</td><td colspan="13"></td></tr>
                <tr><td class="ss-l">… Others</td><td colspan="13"></td></tr>
            </tbody>
        </table>
        @if ($canWrite)<button type="button" class="btn btn-link btn-sm p-0 mb-3" id="ss-add-item">+ Add Item</button>@endif
        <span class="text-muted ms-2" style="font-size:var(--text-xs)">The statement has room for {{ SS::MAX_ITEMS }} items, under Finished goods.</span>

        {{-- Part B.1: age analysis of book debts --}}
        <div class="ss-h">Part B: BOOK DEBTS <span class="ss-hint">(Rs. in thousand) 1. Age analysis of book debts considered good (including bills discounted but excluding deferred receivables)</span></div>
        <table class="ss-t">
            <colgroup>
                <col style="width:30%"><col style="width:8.75%"><col style="width:8.75%"><col style="width:8.75%"><col style="width:8.75%">
                <col style="width:8.75%"><col style="width:8.75%"><col style="width:8.75%"><col style="width:8.75%">
            </colgroup>
            <thead>
                <tr><th rowspan="2">Dues from</th><th colspan="2">Up to 6 months</th><th colspan="2">More than 6 months</th><th colspan="2">Total book debts</th><th colspan="2">Bills discounted with the Bank</th></tr>
                <tr><th>Number</th><th>Amount</th><th>Number</th><th>Amount</th><th>Number</th><th>Amount</th><th>Number</th><th>Amount</th></tr>
            </thead>
            <tbody>
                @foreach (SS::AGE_CATEGORIES as $cat => $label)
                    <tr>
                        <td class="ss-l">{{ $label }}</td>
                        <td>{!! $cell("age_analysis.$cat.up6_number", 'number', 'min="0" step="1"') !!}</td>
                        <td>{!! $cell("age_analysis.$cat.up6_amount", 'number', 'min="0" step="any" data-ss-age-amt="'.$cat.'"') !!}</td>
                        <td>{!! $cell("age_analysis.$cat.more6_number", 'number', 'min="0" step="1"') !!}</td>
                        <td>{!! $cell("age_analysis.$cat.more6_amount", 'number', 'min="0" step="any" data-ss-age-amt="'.$cat.'"') !!}</td>
                        <td class="ss-calc" data-ss-age-total-num="{{ $cat }}">—</td>
                        <td class="ss-calc" data-ss-age-total-amt="{{ $cat }}">—</td>
                        <td>{!! $cell("age_analysis.$cat.bills_number", 'number', 'min="0" step="1"') !!}</td>
                        <td>{!! $cell("age_analysis.$cat.bills_amount", 'number', 'min="0" step="any"') !!}</td>
                    </tr>
                @endforeach
                <tr class="ss-static">
                    <td class="ss-l">Total</td>
                    <td class="ss-calc" id="ss-age-total-up6n">—</td><td class="ss-calc" id="ss-age-total-up6a">—</td>
                    <td class="ss-calc" id="ss-age-total-m6n">—</td><td class="ss-calc" id="ss-age-total-m6a">—</td>
                    <td class="ss-calc" id="ss-age-total-tn">—</td><td class="ss-calc" id="ss-age-total-ta">—</td>
                    <td class="ss-calc" id="ss-age-total-bn">—</td><td class="ss-calc" id="ss-age-total-ba">—</td>
                </tr>
            </tbody>
        </table>

        {{-- Part B.2: Major Debtors --}}
        <div class="ss-h ss-h--sub">2. Only where drawings are permitted directly against book debts <span class="ss-hint">(list of major debtors, i.e., covering at least 60% of total book debts mentioned above)</span></div>
        <table class="ss-t">
            <colgroup><col style="width:34%"><col style="width:16.5%"><col style="width:16.5%"><col style="width:16.5%"><col style="width:16.5%"><col class="ss-xcol"></colgroup>
            <thead>
                <tr><th rowspan="2">Name of the debtor</th><th colspan="2">Outstandings up to 6 months</th><th colspan="2">Outstandings more than 6 months</th><th class="ss-x" rowspan="2"></th></tr>
                <tr><th>Number</th><th>Amount</th><th>Number</th><th>Amount</th></tr>
            </thead>
            <tbody id="ss-debtors">
                <tr class="ss-repeat-row">
                    <td>{!! $cell('debtor_name') !!}</td>
                    <td>{!! $cell('debtor_number', 'number', 'min="0" step="1"') !!}</td>
                    <td>{!! $cell('debtor_amount', 'number', 'min="0" step="any"') !!}</td>
                    <td>{!! $cell('debtor_more6_number', 'number', 'min="0" step="1"') !!}</td>
                    <td>{!! $cell('debtor_more6_amount', 'number', 'min="0" step="any"') !!}</td>
                    <td class="ss-x"></td>
                </tr>
                @php $debtor2Filled = collect(['debtor2_name','debtor2_up6_number','debtor2_up6_amount','debtor2_more6_number','debtor2_more6_amount'])->map(fn ($k) => old("ss.$k", data_get($ss, $k)))->filter()->isNotEmpty(); @endphp
                <tr class="ss-repeat-row {{ ! $debtor2Filled ? 'jb-hidden' : '' }}">
                    <td>{!! $cell('debtor2_name') !!}</td>
                    <td>{!! $cell('debtor2_up6_number', 'number', 'min="0" step="1"') !!}</td>
                    <td>{!! $cell('debtor2_up6_amount', 'number', 'min="0" step="any"') !!}</td>
                    <td>{!! $cell('debtor2_more6_number', 'number', 'min="0" step="1"') !!}</td>
                    <td>{!! $cell('debtor2_more6_amount', 'number', 'min="0" step="any"') !!}</td>
                    <td class="ss-x">{!! $removeBtn !!}</td>
                </tr>
            </tbody>
        </table>
        @if ($canWrite)<button type="button" class="btn btn-link btn-sm p-0 mb-3" id="ss-add-debtor">+ Add Debtor</button>@endif
        <span class="text-muted ms-2" style="font-size:var(--text-xs)">The statement has room for 2 named debtors.</span>

        {{-- Part C: OTHER INFORMATION --}}
        <div class="ss-h">Part C: OTHER INFORMATION</div>
        <table class="ss-t">
            <colgroup><col style="width:34%"><col style="width:22%"><col style="width:22%"><col style="width:22%"></colgroup>
            <thead><tr><th></th><th>Projected for current accounting year</th><th>Up to end of the last month</th><th>During the month under report</th></tr></thead>
            <tbody>
                @foreach (SS::OTHER_INFO_METRICS as $metric => $label)
                    <tr>
                        <td class="ss-l">{{ $label }}</td>
                        @foreach (SS::OTHER_INFO_PERIODS as $period => $periodLabel)
                            <td>{!! $cell("other_info.$metric.$period", 'number', 'min="0" step="any"') !!}</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <td class="ss-l">Sundry Creditors</td>
                    <td>Year end: {!! $cell('sundry_creditors_year_end', 'number', 'min="0" step="any"') !!}</td>
                    <td colspan="2">At the end of the month: <span class="text-muted">(as reported above)</span></td>
                </tr>
            </tbody>
        </table>

        {{-- Notes & Certificates: the template's own static wording, not inputs --}}
        <div class="ss-h ss-h--sub">Notes:</div>
        <p class="ss-note">'A' items should cover at least 60% of the value of stocks under each hand. Other items may be clubbed together.</p>
        <ol class="ss-notes">
            <li>The stocks should be exclusive of materials received for job work but inclusive of materials sent out for processing etc., particulars of the latter should be shown separately indicating quantity, value and names of such processors, etc., along with appropriate certificates from such parties, to the effect that they (i) have received such materials from the borrower for processing, etc., (ii) hold the goods in trust for the bank; and (iii) undertake to deliver such stocks to the Bank or its authorized agents without demur whenever called upon to do so.</li>
            <li>Particulars (items, quantity and value) of stocks more than a year old and included hereinabove should be shown separately.</li>
            <li>Particulars of tailor-made / custom-built products, which are found defective / rejected by the designated buyer should be shown separately.</li>
            <li>Valuation of stocks, etc., should be on the same basis as adopted for the statutory balance sheet and should be applied on a consistent basis. If the market value of any item is less than the value disclosed in this statement, the market value should be indicated by way of a footnote.</li>
            <li>This statement should be furnished for all the units financed by the Bank, unit-wise.</li>
            <li>Where the stock statement represents only a part of total stocks of a borrower, a summary of the total stocks inclusive of stocks shown in this statement should be submitted alongwith this statement classified into raw materials, stocks-in process stores and spares, finished goods and book debts, if necessary e.g. where a single line of activity is financed by the Bank and some other banks; where different lines of activity are financed by the Bank and some other banks.</li>
        </ol>

        <div class="ss-h ss-h--sub">Certificates:</div>
        <p class="ss-note">We hereby certify that –</p>
        <ol class="ss-notes ss-notes--alpha">
            <li>the quantity and quality of the abovenoted stocks and other assets pledged / hypothecated to the Bank are true and that the said stocks and assets are the absolute property of the ………………………………………… company and that the said stocks and assets are not subject to any lien, claim or charges whatsoever.</li>
            <li>The stocks and other assets abovenoted have been valued in the same manner and on the same basis and principles as adopted for the company's last audited and published accounts for the year ended ………………….</li>
            <li>The stocks lying with outside processors included hereinabove and charged to the Bank, the company are in possession of valid documents of title thereto, and are the absolute property of the company and are not subject to any liens, claims or charges whatsoever;</li>
            <li>In the case of stocks and assets shown as being in transit and charged to the Bank, the company are in possession of valid document of title thereto and that the company's title to such stocks &amp; assets is not subject to any liens, claims / charges whatsoever;</li>
            <li>Book debts hypothecated to the Bank shown above are good and do not include any book debts which in our opinion are bad or doubtful of recovery;</li>
            <li>No order of attachment or any notice or process from any Court or any other statutory authorities has been received by the company in respect of the whole or part of the said stocks and assets charged to the Bank;</li>
            <li>The company have complied with all the requirements of ……………………………. (e.g. Cotton Textiles Control Order 1965) and carried out all the instructions issued by the ………………………….. (e.g. the Textile Commissioner) pursuant to the powers vested in him under the same order;</li>
            <li>The above noted stocks and assets (excepting those which are not subject to fire hazards) are fully insured against fire and ………….. risks as per statement of insurance policies attached and that all the conditions and warranties contained in the insurance policies have been complied with and the said policies are valid enforceable;</li>
            <li>The particulars of stocks of assets and all the other information furnished hereinabove have been taken from and are in agreement with the company's books of accounts maintained in the normal course of business and or other books and records maintained in accordance with statutory or other requirements.</li>
        </ol>

        {{-- Summary of stock & book debts position --}}
        <div class="ss-h">Summary of stock &amp; book debts position as per the stock statement dated {!! $cell('statement_date', 'date') !!} <span class="ss-hint">(Rs. {!! $cell('summary_amount') !!} /-)</span></div>
        <table class="ss-t">
            <colgroup><col style="width:5%"><col style="width:27%"><col style="width:15%"><col style="width:10%"><col style="width:6%"><col style="width:12%"><col style="width:12%"><col style="width:13%"></colgroup>
            <thead>
                <tr><th rowspan="2">1</th><th rowspan="2">Items<br><span class="ss-hint">(valuation done at the lowest of cost, market or controlled rates)</span></th><th rowspan="2">Value of stocks</th><th colspan="2">Margin</th><th rowspan="2">Advance value<br>(3-4)</th><th rowspan="2">Sub-limit, if any</th><th rowspan="2">Drawing power</th></tr>
                <tr><th>Amount</th><th>%</th></tr>
            </thead>
            <tbody>
                <tr><td class="ss-c">(i)</td><td class="ss-l">Raw materials</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td></tr>
                <tr><td class="ss-c">(ii)</td><td class="ss-l">Stores</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td></tr>
                <tr><td class="ss-c">(iii)</td><td class="ss-l">Stock-in-process</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td></tr>
                <tr>
                    <td class="ss-c">(iv)</td><td class="ss-l">Finished goods</td><td class="ss-calc" id="ss-sum-stock-value">—</td>
                    <td class="ss-calc" id="ss-sum-stock-margin">—</td><td>{!! $cell('margin_stock', 'number', 'min="0" max="100" step="any" data-ss-sum="stock"') !!}</td>
                    <td class="ss-calc" id="ss-sum-stock-advance">—</td><td>{!! $cell('sub_limit_stock', 'number', 'min="0" step="any"') !!}</td><td class="ss-calc" id="ss-sum-stock-draw">—</td>
                </tr>
                <tr><td class="ss-c">(v)</td><td class="ss-l">Spares</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td><td class="ss-calc">—</td></tr>
                <tr>
                    <td class="ss-c">(vi)</td><td class="ss-l">Book-debts including bills discounted</td><td class="ss-calc" id="ss-sum-debt-value">—</td>
                    <td class="ss-calc" id="ss-sum-debt-margin">—</td><td>{!! $cell('margin_book_debts', 'text', 'data-ss-sum="debt"') !!}</td>
                    <td class="ss-calc" id="ss-sum-debt-advance">—</td><td>{!! $cell('sub_limit_book_debts', 'number', 'min="0" step="any"') !!}</td><td class="ss-calc" id="ss-sum-debt-draw">—</td>
                </tr>
                <tr class="ss-static"><td colspan="2" class="ss-l">Total of (i) to (vi)</td><td class="ss-calc" id="ss-sum-total-value">—</td><td class="ss-calc" id="ss-sum-total-margin">—</td><td></td><td class="ss-calc" id="ss-sum-total-advance">—</td><td class="ss-calc" id="ss-sum-total-sublimit">—</td><td class="ss-calc" id="ss-sum-total-draw">—</td></tr>
            </tbody>
        </table>
        <p class="text-muted mb-0" style="font-size:var(--text-xs)">Raw materials, Stores, Stock-in-process and Spares have no itemised rows in this template (it only itemises Finished goods, in Part A above), so they are left blank rather than guessed.</p>
        <p class="text-muted" style="font-size:var(--text-xs)">Place: {!! $cell('place') !!} &nbsp;&middot;&nbsp; Date: {!! $cell('statement_date', 'date') !!}</p>
    </div>

    <div class="d-flex justify-content-between pt-3 mt-4 border-top">
        <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" class="btn btn-secondary jb-btn-tall">Previous: Documents</button>
        @can('or.view')
            <button type="button" onclick="switchTab('opinion-report-tab', 'opinion-report-panel')" class="btn btn-secondary jb-btn-tall">Next: Opinion Report</button>
        @else
            <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="btn btn-secondary jb-btn-tall">Next: Bank Details</button>
        @endcan
    </div>
</div>

@once
    <style>
        /* The Stock Statement tables only: everything is scoped to .ss-doc inside the Stock Statement panel. */
        #stock-statement-panel .ss-doc { overflow-x: auto; }
        #stock-statement-panel .ss-doc .ss-h { font-weight: 700; font-size: .95rem; color: #000; margin: 1rem 0 .35rem; }
        #stock-statement-panel .ss-doc .ss-h--sub { font-size: .85rem; margin-top: .75rem; }
        #stock-statement-panel .ss-doc .ss-hint { font-weight: 400; font-size: .78rem; color: #333; }
        #stock-statement-panel .ss-doc .ss-t { width: 100%; min-width: 900px; border-collapse: collapse; table-layout: fixed; font-size: .82rem; color: #000; background: #fff; margin: 0 0 .25rem; }
        #stock-statement-panel .ss-doc .ss-t--header { min-width: 0; margin-bottom: .35rem; }
        #stock-statement-panel .ss-doc .ss-t th, #stock-statement-panel .ss-doc .ss-t td { border: 1px solid #000; padding: 2px 4px; vertical-align: middle; background: #fff; }
        #stock-statement-panel .ss-doc .ss-t--header td { border: 0; padding: 1px 2px; }
        #stock-statement-panel .ss-doc .ss-t th { font-weight: 700; text-align: center; }
        #stock-statement-panel .ss-doc .ss-t td.ss-l { font-weight: 600; text-align: left; }
        #stock-statement-panel .ss-doc .ss-t td.ss-c { text-align: center; }
        #stock-statement-panel .ss-doc .ss-t td.ss-calc { text-align: center; color: #555; font-weight: 600; }
        #stock-statement-panel .ss-doc .ss-t tr.ss-static td { font-weight: 700; }
        #stock-statement-panel .ss-doc .ss-t td.ss-strong { font-weight: 700; }
        #stock-statement-panel .ss-doc .ss-t td.ss-hr { font-weight: 400; font-style: italic; }
        #stock-statement-panel .ss-doc .ss-t th.ss-x, #stock-statement-panel .ss-doc .ss-t td.ss-x { border: 0; background: transparent; width: 2rem; text-align: center; padding: 0 0 0 4px; }
        #stock-statement-panel .ss-doc .ss-t col.ss-xcol { width: 2rem; }
        #stock-statement-panel .ss-doc .ss-t .ss-in { display: block; width: 100%; border: 0; border-radius: 0; box-shadow: none; background-color: transparent; padding: 1px 3px; font: inherit; color: inherit; min-height: 0; height: auto; line-height: 1.3; }
        #stock-statement-panel .ss-doc .ss-t .ss-in:focus { background-color: #fffbe6; outline: 1px solid #0d6efd; outline-offset: -1px; box-shadow: none; }
        #stock-statement-panel .ss-doc .ss-t .ss-in:disabled { background-color: #f3f3f3; }
        #stock-statement-panel .ss-doc .ss-remove { border: 0; background: transparent; color: #b02a37; font-size: 1.1rem; line-height: 1; padding: 0 2px; cursor: pointer; }
        #stock-statement-panel .ss-doc .ss-note { font-size: .8rem; margin-bottom: .35rem; }
        #stock-statement-panel .ss-doc .ss-notes { font-size: .78rem; padding-left: 1.5rem; margin-bottom: .75rem; }
        #stock-statement-panel .ss-doc .ss-notes li { margin-bottom: .4rem; }
        #stock-statement-panel .ss-doc .ss-notes--alpha { list-style-type: lower-alpha; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const panel = document.getElementById('stock-statement-panel');
            if (!panel) return;

            // The statement has room for a fixed number of item/debtor rows; "Add" reveals the next.
            [['ss-add-item', 'ss-items'], ['ss-add-debtor', 'ss-debtors']].forEach(([btnId, listId]) => {
                const add = document.getElementById(btnId);
                const list = document.getElementById(listId);
                if (!add || !list) return;
                const hidden = () => list.querySelectorAll('.ss-repeat-row.jb-hidden');
                if (!hidden().length) add.classList.add('jb-hidden');
                add.addEventListener('click', () => {
                    const next = hidden()[0];
                    if (next) next.classList.remove('jb-hidden');
                    if (!hidden().length) add.classList.add('jb-hidden');
                });
            });
            panel.querySelectorAll('.ss-remove').forEach(btn => btn.addEventListener('click', () => {
                const row = btn.closest('.ss-repeat-row');
                row.querySelectorAll('input, textarea, select').forEach(el => { el.value = ''; });
                row.classList.add('jb-hidden');
                const addBtn = document.getElementById('ss-add-debtor');
                if (addBtn) addBtn.classList.remove('jb-hidden');
                totals();
            }));

            // ---- calculated previews (the workbook calculates the same sums) ----
            const num = v => { const n = parseFloat(String(v ?? '').replace(/[^\d.-]/g, '')); return isNaN(n) ? null : n; };
            const fmt = n => n.toLocaleString('en-IN', {maximumFractionDigits: 2});
            const show = (id, value) => { const el = document.getElementById(id) || panel.querySelector('[' + id + ']'); if (el) el.textContent = value === null ? '—' : fmt(value); };

            function totals() {
                // Age analysis: Total book debts per row, and the Total row.
                const sums = {up6n: 0, up6a: 0, m6n: 0, m6a: 0, tn: 0, ta: 0, bn: 0, ba: 0};
                let any = false;
                panel.querySelectorAll('[data-ss-age-total-num]').forEach(el => {
                    const cat = el.dataset.ssAgeTotalNum;
                    const n = num(panel.querySelector(`[name="ss[age_analysis][${cat}][up6_number]"]`)?.value);
                    const m = num(panel.querySelector(`[name="ss[age_analysis][${cat}][more6_number]"]`)?.value);
                    const t = (n !== null || m !== null) ? (n ?? 0) + (m ?? 0) : null;
                    el.textContent = t === null ? '—' : fmt(t);
                    if (t !== null) { sums.tn += t; sums.up6n += n ?? 0; sums.m6n += m ?? 0; any = true; }
                });
                panel.querySelectorAll('[data-ss-age-total-amt]').forEach(el => {
                    const cat = el.dataset.ssAgeTotalAmt;
                    const a = num(panel.querySelector(`[name="ss[age_analysis][${cat}][up6_amount]"]`)?.value);
                    const b = num(panel.querySelector(`[name="ss[age_analysis][${cat}][more6_amount]"]`)?.value);
                    const t = (a !== null || b !== null) ? (a ?? 0) + (b ?? 0) : null;
                    el.textContent = t === null ? '—' : fmt(t);
                    if (t !== null) { sums.ta += t; sums.up6a += a ?? 0; sums.m6a += b ?? 0; any = true; }
                });
                panel.querySelectorAll('[name$="[bills_number]"]').forEach(el => { const n = num(el.value); if (n !== null) { sums.bn += n; any = true; } });
                panel.querySelectorAll('[name$="[bills_amount]"]').forEach(el => { const n = num(el.value); if (n !== null) { sums.ba += n; any = true; } });
                ['up6n', 'up6a', 'm6n', 'm6a', 'tn', 'ta', 'bn', 'ba'].forEach(k => {
                    const el = document.getElementById('ss-age-total-' + k);
                    if (el) el.textContent = any ? fmt(sums[k]) : '—';
                });

                // Summary table: finished-goods value/margin/advance/drawing-power, book-debts value/margin/advance/drawing-power.
                const itemValues = [];
                panel.querySelectorAll('#ss-items .ss-repeat-row').forEach(row => {
                    const b = num(row.querySelector('[name$="[opening_qty]"]')?.value);
                    const c = num(row.querySelector('[name$="[opening_rate]"]')?.value);
                    const e2 = num(row.querySelector('[name$="[in_qty]"]')?.value);
                    const f = num(row.querySelector('[name$="[in_rate]"]')?.value);
                    const h = num(row.querySelector('[name$="[out_qty]"]')?.value);
                    const i = num(row.querySelector('[name$="[out_rate]"]')?.value);
                    if (b === null && e2 === null && h === null) return;
                    const closingQty = (b ?? 0) + (e2 ?? 0) - (h ?? 0);
                    if (i !== null) itemValues.push(closingQty * i);
                });
                const stockValue = itemValues.length ? itemValues.reduce((a, c) => a + c, 0) : null;
                show('ss-sum-stock-value', stockValue);
                const marginPct = num(panel.querySelector('[name="ss[margin_stock]"]')?.value);
                let stockMargin = null, stockAdvance = null;
                if (stockValue !== null && marginPct !== null) {
                    stockMargin = Math.round(stockValue * marginPct / 100);
                    stockAdvance = stockValue - stockMargin;
                }
                show('ss-sum-stock-margin', stockMargin);
                show('ss-sum-stock-advance', stockAdvance);
                show('ss-sum-stock-draw', stockAdvance);

                const debtor1 = num(panel.querySelector('[name="ss[debtor_amount]"]')?.value);
                const debtor2 = num(panel.querySelector('[name="ss[debtor2_up6_amount]"]')?.value);
                const debtValue = (debtor1 !== null || debtor2 !== null) ? (debtor1 ?? 0) + (debtor2 ?? 0) : null;
                show('ss-sum-debt-value', debtValue);
                let debtMargin = null, debtAdvance = null;
                if (debtValue !== null) {
                    debtMargin = Math.round(debtValue * 0.5);
                    debtAdvance = debtValue - debtMargin;
                }
                show('ss-sum-debt-margin', debtMargin);
                show('ss-sum-debt-advance', debtAdvance);
                show('ss-sum-debt-draw', debtAdvance);

                const sumOrNull = (...vals) => { const present = vals.filter(v => v !== null); return present.length ? present.reduce((a, c) => a + c, 0) : null; };
                show('ss-sum-total-value', sumOrNull(stockValue, debtValue));
                show('ss-sum-total-margin', sumOrNull(stockMargin, debtMargin));
                show('ss-sum-total-advance', sumOrNull(stockAdvance, debtAdvance));
                show('ss-sum-total-draw', sumOrNull(stockAdvance, debtAdvance));
                const subStock = num(panel.querySelector('[name="ss[sub_limit_stock]"]')?.value);
                const subDebt = num(panel.querySelector('[name="ss[sub_limit_book_debts]"]')?.value);
                show('ss-sum-total-sublimit', sumOrNull(subStock, subDebt));
            }
            panel.addEventListener('input', totals);
            totals();

            // ---- other tabs -> Stock Statement live linking (mapping: StockStatementData::LEAD_LINKS) ----
            const config = @json(\App\Support\StockStatementData::linkConfig($saved));
            const leadForm = panel.closest('form');
            const manual = new Set(config.overrides);
            const clean = v => (v ?? '').toString().trim();

            function sourceValue(name) {
                const el = leadForm ? leadForm.querySelector('[name="' + name + '"]') : null;
                if (!el) return '';
                if (el.tagName === 'SELECT') return el.value === '' ? '' : clean(el.options[el.selectedIndex]?.text);
                return clean(el.value);
            }
            const derived = link => link.sources.map(sourceValue).find(v => v !== '') ?? '';
            const control = path => panel.querySelector('[name="ss[' + path + ']"]');

            config.links.forEach(link => {
                const c = control(link.path);
                if (c && clean(c.value) !== '' && clean(c.value) !== derived(link)) manual.add(link.path);
            });
            const sync = () => config.links.forEach(link => {
                const c = control(link.path);
                if (c && !manual.has(link.path)) c.value = derived(link);
            });
            sync();

            const sourceNames = new Set(config.links.flatMap(l => l.sources));
            if (leadForm) {
                const onLead = e => { if (!panel.contains(e.target) && sourceNames.has(e.target.name)) sync(); };
                leadForm.addEventListener('input', onLead);
                leadForm.addEventListener('change', onLead);
            }
            const byName = Object.fromEntries(config.links.map(l => ['ss[' + l.path + ']', l.path]));
            const mark = e => { const p = byName[e.target.name]; if (p) manual.add(p); };
            panel.addEventListener('input', mark);
            panel.addEventListener('change', mark);

            // Generation uses the saved statement; warn about unsaved edits.
            let dirty = false;
            panel.addEventListener('input', () => dirty = true);
            const gen = document.getElementById('ss-generate-btn');
            if (gen) gen.addEventListener('click', e => {
                if (dirty && !confirm('You have unsaved changes that will not be included. Generate from the last saved version?')) e.preventDefault();
            });
        });
    </script>
@endonce
