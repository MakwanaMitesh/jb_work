{{-- CMA tab panel: the customer/profile values printed on the CMA workbook's profile sheet. The financial
     statements, ratios and formulas stay in the workbook and are not asked for here. $lead may be null (Create
     Lead). Controls are plain members of the lead <form> (names cma[...]), so one submit saves the lead and
     this tab together. --}}
@php
    use App\Support\CmaProfileData as CMA;
    use Illuminate\Support\HtmlString;

    $lead = $lead ?? null;
    $exists = (bool) $lead?->exists;
    $cma = $exists ? CMA::for($lead) : CMA::normalize([]);
    $saved = $exists ? $lead->cmaProfile : null;
    $canWrite = auth()->user()->can($saved?->saved_at ? 'cma.update' : 'cma.create');
    $hasFile = $exists && \Illuminate\Support\Facades\Storage::disk('public')->exists('cma/' . $lead->id . '/CMA.xlsx');
    $cmaErrors = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'cma.'))->map(fn ($k) => $errors->first($k))->unique()->values();

    $e = fn ($v) => e((string) $v);
    $field = function (string $key, string $col = 'col-12 col-md-4', string $type = 'text') use ($cma, $e, $canWrite) {
        [$label, , $long] = CMA::FIELDS[$key];
        $value = old('cma.' . $key, $cma[$key] ?? null);
        $common = 'id="cma_' . $key . '" name="cma[' . $key . ']" ' . ($canWrite ? '' : 'disabled');
        $control = $long
            ? '<textarea ' . $common . ' rows="2" class="form-control form-control-sm jb-input">' . $e($value) . '</textarea>'
            : '<input type="' . $type . '" ' . $common . ' value="' . $e($value) . '" class="form-control form-control-sm jb-input">';

        return new HtmlString('<div class="' . $col . '"><label class="form-label mb-1" for="cma_' . $key . '" style="font-size:var(--text-xs);font-weight:600;color:var(--slate-600)">' . $e($label) . '</label>' . $control . '</div>');
    };
@endphp

<div id="cma-panel" class="tab-panel jb-hidden">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom pb-2 mb-3">
        <div>
            <h3 class="jb-section-title m-0">CMA</h3>
            <p class="jb-muted mb-0" style="font-size:var(--text-xs)">Only the customer / profile details printed on the CMA are asked for here; its financial statements and ratios calculate in the workbook. Fields from the Details Sheet and Business Details fill in automatically. Saved together with the lead.</p>
        </div>
        @if ($exists)
            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('cma.generate')
                    <button type="submit" form="cma-generate" class="btn btn-outline-primary btn-sm" id="cma-generate-btn">Generate CMA</button>
                @endcan
                @can('cma.download')
                    @if ($hasFile)
                        <a href="{{ route('admin.leads.cma.download', $lead) }}" class="btn btn-success btn-sm">Download CMA</a>
                    @endif
                @endcan
            </div>
        @endif
    </div>

    @if ($cmaErrors->isNotEmpty())
        <div class="alert alert-danger py-2 mb-3" role="alert">
            <strong>Please correct the following:</strong>
            <ul class="mb-0">@foreach ($cmaErrors as $msg)<li>{{ $msg }}</li>@endforeach</ul>
        </div>
    @endif

    <h4 class="jb-section-title mb-2">Business / Applicant Information</h4>
    <div class="row g-3 mb-4">
        {!! $field('activity') !!}
        {!! $field('firm_name') !!}
        {!! $field('proprietor_name') !!}
        {!! $field('pan', 'col-12 col-md-3') !!}
        {!! $field('udyam_no', 'col-12 col-md-3') !!}
        {!! $field('mobile', 'col-12 col-md-3', 'tel') !!}
        {!! $field('email', 'col-12 col-md-3', 'email') !!}
        {!! $field('address', 'col-12') !!}
    </div>

    <h4 class="jb-section-title mb-2">Business Profile</h4>
    <div class="row g-3 mb-4">
        {!! $field('business_experience') !!}
        {!! $field('area_of_operation') !!}
        {!! $field('manpower') !!}
    </div>

    <h4 class="jb-section-title mb-2">Infrastructure</h4>
    <div class="row g-3 mb-2">
        {!! $field('space') !!}
        {!! $field('connectivity') !!}
        {!! $field('location') !!}
        {!! $field('electricity_water') !!}
        {!! $field('existing_facility') !!}
    </div>

    <div class="d-flex justify-content-between pt-3 mt-4 border-top">
        @can('or.view')
            <button type="button" onclick="switchTab('opinion-report-tab', 'opinion-report-panel')" class="btn btn-secondary jb-btn-tall">Previous: Opinion Report</button>
        @elsecan('sbi_stock_statement.view')
            <button type="button" onclick="switchTab('stock-statement-tab', 'stock-statement-panel')" class="btn btn-secondary jb-btn-tall">Previous: Stock Statement</button>
        @else
            <button type="button" onclick="switchTab('documents-tab', 'documents-panel')" class="btn btn-secondary jb-btn-tall">Previous: Documents</button>
        @endcan
        @can('dpr.view')
            <button type="button" onclick="switchTab('dpr-tab', 'dpr-panel')" class="btn btn-secondary jb-btn-tall">Next: DPR</button>
        @else
            <button type="button" onclick="switchTab('bank-tab', 'bank-panel')" class="btn btn-secondary jb-btn-tall">Next: Bank Details</button>
        @endcan
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const panel = document.getElementById('cma-panel');
            if (!panel) return;

            // ---- other tabs -> CMA live linking (mapping: CmaProfileData::LEAD_LINKS) ----
            const config = @json(\App\Support\CmaProfileData::linkConfig($saved));
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
            const control = path => panel.querySelector('[name="cma[' + path + ']"]');

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
            const byName = Object.fromEntries(config.links.map(l => ['cma[' + l.path + ']', l.path]));
            const mark = e => { const p = byName[e.target.name]; if (p) manual.add(p); };
            panel.addEventListener('input', mark);
            panel.addEventListener('change', mark);

            // Generation uses the saved profile; warn about unsaved edits.
            let dirty = false;
            panel.addEventListener('input', () => dirty = true);
            const gen = document.getElementById('cma-generate-btn');
            if (gen) gen.addEventListener('click', e => {
                if (dirty && !confirm('You have unsaved changes that will not be included. Generate from the last saved version?')) e.preventDefault();
            });
        });
    </script>
@endonce
