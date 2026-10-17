<x-admin-bootstrap-layout title="Contact Enquiries">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.contact-enquiries.index') }}">Contact Enquiries</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">List</span>
        </nav>

        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="jb-page-title">Contact Enquiries</h1>
                <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">Public website submissions and loan consultancy inquiries.</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="jb-alert jb-alert-success fw-medium mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="jb-alert jb-alert-danger fw-medium mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Summary Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="jb-card p-3 text-center">
                <span class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--slate-500);letter-spacing:0.05em">Total</span>
                <span class="d-block fw-bold mt-1" style="font-size:var(--text-2xl);color:var(--slate-900)">{{ $stats['total'] }}</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="jb-card p-3 text-center border-start border-4 border-primary">
                <span class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--primary-600);letter-spacing:0.05em">New</span>
                <span class="d-block fw-bold mt-1 text-primary" style="font-size:var(--text-2xl)">{{ $stats['new'] }}</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="jb-card p-3 text-center border-start border-4 border-warning">
                <span class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--amber-600, #d97706);letter-spacing:0.05em">Contacted</span>
                <span class="d-block fw-bold mt-1 text-warning" style="font-size:var(--text-2xl)">{{ $stats['contacted'] }}</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="jb-card p-3 text-center border-start border-4 border-info">
                <span class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--purple-600, #7c3aed);letter-spacing:0.05em">Follow-up</span>
                <span class="d-block fw-bold mt-1 text-info" style="font-size:var(--text-2xl)">{{ $stats['follow_up'] }}</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="jb-card p-3 text-center border-start border-4 border-success">
                <span class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--emerald-600);letter-spacing:0.05em">Converted</span>
                <span class="d-block fw-bold mt-1 text-success" style="font-size:var(--text-2xl)">{{ $stats['converted'] }}</span>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="jb-card p-3 text-center border-start border-4 border-secondary">
                <span class="d-block text-uppercase fw-semibold" style="font-size:var(--text-xs);color:var(--slate-600);letter-spacing:0.05em">Closed</span>
                <span class="d-block fw-bold mt-1 text-secondary" style="font-size:var(--text-2xl)">{{ $stats['closed'] }}</span>
            </div>
        </div>
    </div>

    <div class="jb-card overflow-hidden">
        <div class="jb-card-header d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-center">
            <form action="{{ route('admin.contact-enquiries.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-3 w-100 w-sm-auto">
                <div class="jb-search">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--slate-400);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, mobile, email, city...">
                </div>
                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto">
                    <option value="">All Statuses</option>
                    @foreach (\App\Models\ContactEnquiry::STATUSES as $val => $lbl)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.contact-enquiries.index') }}" class="jb-file-link-muted fw-medium">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="jb-table jb-table-compact">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Loan Requirement</th>
                        <th>Status</th>
                        <th>Submitted Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enquiries as $enquiry)
                        <tr>
                            <td class="jb-cell-strong">
                                <a href="{{ route('admin.contact-enquiries.show', $enquiry) }}" class="text-decoration-none text-reset fw-semibold">
                                    {{ $enquiry->full_name }}
                                </a>
                            </td>
                            <td>
                                <a href="tel:{{ $enquiry->mobile }}" class="text-decoration-none text-reset">
                                    {{ $enquiry->mobile }}
                                </a>
                            </td>
                            <td>{{ $enquiry->email ?? '—' }}</td>
                            <td>{{ $enquiry->city ?? '—' }}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 180px;" title="{{ $enquiry->loan_requirement }}">
                                    {{ $enquiry->loan_requirement ?? '—' }}
                                </span>
                            </td>
                            <td>
                                @can('contact_enquiry.update')
                                    <form method="POST" action="{{ route('admin.contact-enquiries.status', $enquiry) }}" class="d-inline-block">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;font-size:var(--text-xs)">
                                            @foreach (\App\Models\ContactEnquiry::STATUSES as $val => $lbl)
                                                <option value="{{ $val }}" @selected($enquiry->status === $val)>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <span class="jb-badge {{ $enquiry->status_badge_class }}">
                                        {{ $enquiry->status }}
                                    </span>
                                @endcan
                            </td>
                            <td>{{ $enquiry->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="jb-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 8.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM10 14a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" /></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end text-start text-nowrap" style="min-width:12rem">
                                        <li>
                                            <a href="{{ route('admin.contact-enquiries.show', $enquiry) }}" class="dropdown-item">
                                                <span>View details</span>
                                            </a>
                                        </li>
                                        @can('contact_enquiry.delete')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.contact-enquiries.destroy', $enquiry) }}" onsubmit="return confirm('Are you sure you want to delete this enquiry?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item dropdown-item-danger">
                                                        <span>Delete</span>
                                                    </button>
                                                </form>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5" style="color:var(--slate-400)">No contact enquiries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enquiries->hasPages())
            <div class="p-3 border-top">
                {{ $enquiries->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</x-admin-bootstrap-layout>
