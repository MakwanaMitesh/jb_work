{{-- Form shell for the Generate button in the Application Form tab. The tab sits inside the lead's
     own form, so the button attaches to this one via form="…" (HTML forms cannot nest). --}}
@can('application_form.generate')
    <form id="application-form-generate" method="POST" action="{{ route('admin.leads.application-form.generate', $lead) }}">
        @csrf
    </form>
@endcan

@can('sbi_stock_statement.generate')
    <form id="stock-statement-generate" method="POST" action="{{ route('admin.leads.sbi-stock-statement.generate', $lead) }}">
        @csrf
    </form>
@endcan

@can('or.generate')
    <form id="opinion-report-generate" method="POST" action="{{ route('admin.leads.or.generate', $lead) }}">
        @csrf
    </form>
@endcan

@can('cma.generate')
    <form id="cma-generate" method="POST" action="{{ route('admin.leads.cma.generate', $lead) }}">
        @csrf
    </form>
@endcan

@can('dpr.generate')
    <form id="dpr-generate" method="POST" action="{{ route('admin.leads.dpr.generate', $lead) }}">
        @csrf
    </form>
@endcan
