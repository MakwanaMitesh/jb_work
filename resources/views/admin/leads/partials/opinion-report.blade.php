{{-- Opinion Report tab panel: only the yellow (dynamic) values of the OR / net-worth affidavit. $lead may be
     null (Create Lead). Controls are plain members of the lead <form> (names opr[...]), so one submit saves
     the lead and this tab together. Age is never entered: it is calculated from the date of birth. --}}
@php
    use App\Support\OpinionReportData as ORD;
    use Illuminate\Support\HtmlString;

    $lead = $lead ?? null;
    $exists = (bool) $lead?->exists;
    $or = $exists ? ORD::for($lead) : ORD::normalize([]);
    $saved = $exists ? $lead->opinionReport : null;
    $canWrite = auth()->user()->can($saved?->saved_at ? 'or.update' : 'or.create');
    $hasFile = $exists && \Illuminate\Support\Facades\Storage::disk('public')->exists('or/' . $lead->id . '/OR.docx');
    $orErrors = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'opr.'))->map(fn ($k) => $errors->first($k))->unique()->values();

    $e = fn ($v) => e((string) $v);
    $name = fn (string $path) => 'opr[' . str_replace('.', '][', $path) . ']';
    $val = fn (string $path) => old('opr.' . $path, data_get($or, $path));
    $dis = $canWrite ? '' : 'disabled';

    $control = function (string $path, string $type = 'text', string $extra = '', array $options = []) use ($name, $val, $e, $dis) {
        $id = 'opr_' . str_replace('.', '_', $path);
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
        '<div class="' . $col . '"><label class="form-label mb-1" for="opr_' . str_replace('.', '_', $path) . '" style="font-size:var(--text-xs);font-weight:600;color:var(--slate-600)">' . $e($label) . '</label>' . $control($path, $type, $extra, $options) . '</div>'
    );

    $deponentAge = ORD::ageFrom($or['date_of_birth']);
    $rowFilled = fn (string $group, int $i) => collect(old("opr.$group.$i", data_get($or, "$group.$i", [])))->filter()->isNotEmpty();
@endphp

<div id="opinion-report-panel" class="tab-panel jb-hidden">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-2 mb-3">
        <div>
            <h3 class="jb-section-title m-0">Opinion Report</h3>
            <p class="jb-muted mb-0" style="font-size:var(--text-xs)">Only the highlighted (dynamic) values of the affidavit are asked for here; the wording itself is fixed. Fields already in the Details Sheet or Bank Loan tab fill in automatically. Saved together with the lead.</p>
        </div>
        @if ($exists)
            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('or.generate')
                    <button type="submit" form="opinion-report-generate" class="btn btn-outline-primary btn-sm" id="opr-generate-btn">Generate Opinion Report</button>
                @endcan
                @can('or.download')
                    @if ($hasFile)
                        <a href="{{ route('admin.leads.or.download', $lead) }}" class="btn btn-success btn-sm">Download Opinion Report</a>
                    @endif
                @endcan
            </div>
        @endif
    </div>

    @if ($orErrors->isNotEmpty())
        <div class="alert alert-danger py-2 mb-3" role="alert">
            <strong>Please correct the following:</strong>
            <ul class="mb-0">@foreach ($orErrors as $msg)<li>{{ $msg }}</li>@endforeach</ul>
        </div>
    @endif

    <h4 class="jb-section-title mb-2">Personal / Deponent Details</h4>
    <div class="row g-3 mb-4">
        {!! $field('deponent_name', 'Deponent / Person Full Name', 'text', 'col-12 col-md-5') !!}
        {!! $field('relationship', 'Son / Daughter / Wife of', 'select', 'col-12 col-md-2', '', ORD::RELATIONSHIPS) !!}
        {!! $field('father_name', 'Father / Mother / Spouse Full Name', 'text', 'col-12 col-md-5') !!}
        {!! $field('date_of_birth', 'Date of Birth', 'date', 'col-6 col-md-3') !!}
        <div class="col-6 col-md-2">
            <label class="form-label mb-1" for="opr_age" style="font-size:var(--text-xs);font-weight:600;color:var(--slate-600)">Age <span class="text-muted fw-normal">(from date of birth)</span></label>
            <input type="text" id="opr_age" class="form-control form-control-sm jb-input" value="{{ $deponentAge }}" readonly tabindex="-1">
        </div>
        {!! $field('place_of_birth', 'Place of Birth', 'text', 'col-12 col-md-3') !!}
        {!! $field('occupation', 'Occupation', 'text', 'col-12 col-md-4') !!}
        {!! $field('residential_address', 'Residential Address', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('office_address', 'Office Address', 'textarea', 'col-12 col-md-6') !!}
    </div>

    <h4 class="jb-section-title mb-2">Bank &amp; Borrower Details</h4>
    <div class="row g-3 mb-4">
        {!! $field('branch', 'State Bank of India — Branch', 'text', 'col-12 col-md-4') !!}
        {!! $field('borrower_name', 'Borrower Name (M/s)', 'text', 'col-12 col-md-5') !!}
        {!! $field('assets_date', 'Assets & Liabilities Statement Date (date of swearing)', 'date', 'col-12 col-md-3') !!}
        <div class="col-12"><span class="text-muted" style="font-size:var(--text-xs)">The branch is entered once and used everywhere the document mentions it. The bank itself is fixed wording in the document.</span></div>
    </div>

    <h4 class="jb-section-title mb-2">Immovable Properties in India</h4>
    <div id="opr-properties" class="d-flex flex-column gap-2 mb-2">
        @for ($i = 0; $i < ORD::MAX_PROPERTIES; $i++)
            <div class="opr-repeat border rounded p-2 {{ $i > 0 && ! $rowFilled('properties', $i) ? 'jb-hidden' : '' }}">
                <div class="row g-2">
                    {!! $field("properties.$i.place", 'Place where land / building is situated', 'text', 'col-12 col-md-4') !!}
                    {!! $field("properties.$i.ownership_origin", 'Ancestral / Self acquired', 'select', 'col-6 col-md-2', '', ORD::OWNERSHIP_ORIGINS) !!}
                    {!! $field("properties.$i.ownership_type", 'Individual / Joint names', 'select', 'col-6 col-md-2', '', ORD::OWNERSHIP_TYPES) !!}
                    {!! $field("properties.$i.encumbered", 'Encumbered (to what extent)', 'text', 'col-12 col-md-4') !!}
                    {!! $field("properties.$i.extent_acres", 'Extent of land (acres)', 'text', 'col-6 col-md-2') !!}
                    {!! $field("properties.$i.market_value", 'Market value (INR)', 'text', 'col-6 col-md-2') !!}
                    {!! $field("properties.$i.case_suit", 'Any case / suit before a court', 'text', 'col-12 col-md-2') !!}
                    {!! $field("properties.$i.land_type", 'Agricultural / Non agricultural', 'select', 'col-6 col-md-3', '', ORD::LAND_TYPES) !!}
                    {!! $field("properties.$i.ceiling", 'Ceiling area', 'select', 'col-6 col-md-3', '', ORD::CEILING) !!}
                </div>
            </div>
        @endfor
    </div>
    @if ($canWrite)<button type="button" class="btn btn-link btn-sm p-0 opr-add" data-target="opr-properties">+ Add Property</button>@endif

    <div class="row g-3 mt-1 mb-4">
        {!! $field('outside_india', 'Immovable property outside India', 'textarea', 'col-12') !!}
    </div>

    <h4 class="jb-section-title mb-2">Liquid Assets</h4>
    <div class="table-responsive mb-4">
        <table class="table table-sm align-middle mb-1" style="min-width:720px">
            <thead><tr style="font-size:var(--text-xs)"><th>Particulars</th><th>Description</th><th>Amount</th><th>Encumbered (to what extent)</th></tr></thead>
            <tbody>
                @foreach (ORD::LIQUID_ASSETS as $key => $label)
                    <tr>
                        <td class="fw-semibold" style="font-size:var(--text-sm)">{{ $label }}</td>
                        <td>{!! $control("liquid_assets.$key.description") !!}</td>
                        <td>{!! $control("liquid_assets.$key.amount") !!}</td>
                        <td>{!! $control("liquid_assets.$key.encumbered") !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <span class="text-muted" style="font-size:var(--text-xs)">Leave Description empty to keep the document's own wording (Cash / Equity / Stock).</span>
    </div>

    <h4 class="jb-section-title mb-2">Other Assets</h4>
    <div class="row g-3 mb-4">
        {!! $field('gov_securities', 'Government securities (full details)', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('shares', 'Shares (full details)', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('life_insurance', 'Life insurance policies — details', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('life_policy_numbers', 'Policy number(s)', 'text', 'col-12 col-md-3') !!}
        {!! $field('life_surrender_value', 'Surrender value', 'text', 'col-12 col-md-3') !!}
        {!! $field('jewellery', 'Jewellery', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('associate_investment', 'Investment in business in associate firm', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('intangible_assets', 'Intangible assets', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('income_per_month', 'Income per month', 'text', 'col-12 col-md-6') !!}
        {!! $field('other_asset', 'Any other asset not covered above', 'textarea', 'col-12') !!}
    </div>

    <h4 class="jb-section-title mb-2">Liabilities</h4>
    <div class="row g-3 mb-4">
        {!! $field('borrowings_banks', 'Borrowings from banks', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('borrowings_market', 'Borrowings from market', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('borrowings_other', 'Borrowings from other sources', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('indirect_liability', 'Assumed indirect liability', 'textarea', 'col-12 col-md-6') !!}
        {!! $field('other_liability', 'Any other liability not covered above', 'textarea', 'col-12') !!}
    </div>

    <h4 class="jb-section-title mb-2">Family Members / Legal Heirs</h4>
    <div class="table-responsive mb-1">
        <table class="table table-sm align-middle mb-1" style="min-width:900px">
            <thead><tr style="font-size:var(--text-xs)"><th style="width:3rem">SL</th><th>Name</th><th>Date of Birth <span class="fw-normal">(optional)</span></th><th style="width:6rem">Age</th><th>Occupation</th><th>Relation</th><th>Address</th></tr></thead>
            <tbody id="opr-family">
                @for ($i = 0; $i < ORD::MAX_FAMILY; $i++)
                    <tr class="opr-repeat {{ $i > 0 && ! $rowFilled('family_members', $i) ? 'jb-hidden' : '' }}">
                        <td class="text-muted">{{ $i + 1 }}</td>
                        <td>{!! $control("family_members.$i.name") !!}</td>
                        <td>{!! $control("family_members.$i.date_of_birth", 'date', 'data-opr-dob') !!}</td>
                        <td>{!! $control("family_members.$i.age", 'text', 'data-opr-age') !!}</td>
                        <td>{!! $control("family_members.$i.occupation") !!}</td>
                        <td>{!! $control("family_members.$i.relation") !!}</td>
                        <td>{!! $control("family_members.$i.address") !!}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
    @if ($canWrite)<button type="button" class="btn btn-link btn-sm p-0 opr-add" data-target="opr-family">+ Add Family Member</button>@endif
    <div class="mb-4"></div>

    <h4 class="jb-section-title mb-2">Declaration / Date</h4>
    <div class="row g-3 mb-2">
        {!! $field('deponent_date', 'Deponent Date', 'date', 'col-12 col-md-3') !!}
        {!! $field('notify_by', 'Notify the bank of changes every year by', 'text', 'col-12 col-md-5') !!}
    </div>

    <div class="d-flex justify-content-between pt-3 mt-4 border-top">
        @can('sbi_stock_statement.view')
            <button type="button" onclick="switchTab('stock-statement-tab', 'stock-statement-panel')" class="btn btn-secondary jb-btn-tall">Previous: Stock Statement</button>
        @else
            <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" class="btn btn-secondary jb-btn-tall">Previous: Documents</button>
        @endcan
        @can('cma.view')
            <button type="button" onclick="switchTab('cma-tab', 'cma-panel')" class="btn btn-secondary jb-btn-tall">Next: CMA</button>
        @else
            <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="btn btn-secondary jb-btn-tall">Next: Bank Details</button>
        @endcan
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const panel = document.getElementById('opinion-report-panel');
            if (!panel) return;

            // "+ Add ..." reveals the next hidden row of its list.
            panel.querySelectorAll('.opr-add').forEach(btn => {
                const list = document.getElementById(btn.dataset.target);
                const hidden = () => list.querySelectorAll('.opr-repeat.jb-hidden');
                if (!hidden().length) btn.classList.add('jb-hidden');
                btn.addEventListener('click', () => {
                    const next = hidden()[0];
                    if (next) next.classList.remove('jb-hidden');
                    if (!hidden().length) btn.classList.add('jb-hidden');
                });
            });

            // Age is calculated from the date of birth (deponent and, when given, each family member).
            const ageFrom = value => {
                if (!value) return '';
                const d = new Date(value + 'T00:00:00');
                if (isNaN(d)) return '';
                const now = new Date();
                let age = now.getFullYear() - d.getFullYear();
                if (now.getMonth() < d.getMonth() || (now.getMonth() === d.getMonth() && now.getDate() < d.getDate())) age--;
                return age >= 0 ? String(age) : '';
            };
            const deponentDob = panel.querySelector('[name="opr[date_of_birth]"]');
            const deponentAge = document.getElementById('opr_age');
            if (deponentDob) {
                const show = () => { deponentAge.value = ageFrom(deponentDob.value); };
                deponentDob.addEventListener('input', show);
                deponentDob.addEventListener('change', show);
                // The date of birth can also arrive from the Details Sheet, which is outside this panel.
                if (deponentDob.form) {
                    deponentDob.form.addEventListener('input', show);
                    deponentDob.form.addEventListener('change', show);
                }
                show();
            }
            panel.querySelectorAll('[data-opr-dob]').forEach(dob => {
                const age = dob.closest('tr').querySelector('[data-opr-age]');
                const apply = () => {
                    const computed = ageFrom(dob.value);
                    if (computed !== '') { age.value = computed; age.readOnly = true; } else { age.readOnly = false; }
                };
                dob.addEventListener('input', apply);
                dob.addEventListener('change', apply);
                apply();
            });

            // ---- other tabs -> Opinion Report live linking (mapping: OpinionReportData::LEAD_LINKS) ----
            const config = @json(\App\Support\OpinionReportData::linkConfig($saved));
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
            const control = path => panel.querySelector('[name="opr[' + path + ']"]');

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
            const byName = Object.fromEntries(config.links.map(l => ['opr[' + l.path + ']', l.path]));
            const mark = e => { const p = byName[e.target.name]; if (p) manual.add(p); };
            panel.addEventListener('input', mark);
            panel.addEventListener('change', mark);

            // Generation uses the saved report; warn about unsaved edits.
            let dirty = false;
            panel.addEventListener('input', () => dirty = true);
            const gen = document.getElementById('opr-generate-btn');
            if (gen) gen.addEventListener('click', e => {
                if (dirty && !confirm('You have unsaved changes that will not be included. Generate from the last saved version?')) e.preventDefault();
            });
        });
    </script>
@endonce
