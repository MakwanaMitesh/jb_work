@php $editing = $stage->exists; @endphp
<x-admin-bootstrap-layout :title="$editing ? 'Edit Stage — ' . $stage->name : 'New Workflow Stage'">
    <div class="mb-4">
        <nav class="jb-breadcrumb">
            <a href="{{ route('admin.workflow.index') }}">Lead Workflow</a>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" style="opacity:.6"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
            <span class="current">{{ $editing ? 'Edit' : 'Create' }}</span>
        </nav>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.workflow.index') }}" class="jb-back-btn">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="jb-page-title">{{ $editing ? 'Edit Stage' : 'New Stage' }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ $editing ? route('admin.workflow.update', $stage) : route('admin.workflow.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="jb-card p-4 p-sm-5 mb-4">
            <div class="row g-4">
                <div class="col-12 col-md-5">
                    <x-input-label-bs for="name" value="Stage Name" :required="true" />
                    <x-text-input-bs id="name" type="text" name="name" :value="old('name', $stage->name)" placeholder="e.g. Initial Verification" required />
                    <x-input-error-bs :messages="$errors->get('name')" />
                </div>
                <div class="col-12 col-md-4">
                    <x-input-label-bs for="role_id" value="Role" :required="true" />
                    <select id="role_id" name="role_id" class="form-select jb-input" required>
                        <option value="">Select Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((int) old('role_id', $stage->role_id) === $role->id)>
                                {{ $role->name }} ({{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}){{ $role->is_active ? '' : ' — inactive' }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error-bs :messages="$errors->get('role_id')" />
                    <p class="jb-form-help m-0 mt-1">Users get this role under Employees. Create new roles under Employee Roles.</p>
                </div>
                <div class="col-6 col-md-2">
                    <x-input-label-bs for="sequence" value="Step Order" />
                    <x-text-input-bs id="sequence" type="number" min="1" name="sequence" :value="old('sequence', $stage->sequence)" />
                    <x-input-error-bs :messages="$errors->get('sequence')" />
                </div>
                <div class="col-6 col-md-1 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $stage->is_active))>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
        </div>

        @php
            $selected = old('modules', $stage->modules ?? []);
            $selectedView = old('view_modules', $stage->view_modules ?? []);
        @endphp

        <div class="jb-card p-4 p-sm-5 mb-4">
            <h3 class="jb-section-title mb-1">Tabs This Stage Fills In</h3>
            <p class="jb-muted mb-3" style="font-size:var(--text-sm)">
                Users of this role can edit only these tabs, and must complete them all before they can transfer the lead. A tab belongs to one stage only.
            </p>
            <div class="d-flex flex-wrap gap-2">
                @foreach (\App\Support\LeadTabs::TABS as $key => $tab)
                    @php $owner = $takenTabs[$key] ?? null; @endphp
                    <label class="jb-perm-chip" @if ($owner) title="Already filled in by '{{ $owner }}'" style="opacity:.5;cursor:not-allowed" @endif>
                        <input type="checkbox" class="form-check-input m-0" name="modules[]" value="{{ $key }}"
                            @checked(in_array($key, $selected, true)) @disabled($owner)>
                        <span>{{ $tab['label'] }}</span>
                        @if ($owner)<span class="jb-muted" style="font-size:var(--text-xs)">({{ $owner }})</span>@endif
                    </label>
                @endforeach
            </div>
            <x-input-error-bs :messages="$errors->get('modules')" />
        </div>

        <div class="jb-card p-4 p-sm-5 mb-4">
            <h3 class="jb-section-title mb-1">Read-only Tabs <span class="jb-muted fw-normal">(optional)</span></h3>
            <p class="jb-muted mb-3" style="font-size:var(--text-sm)">
                Tabs from other stages this role may look at but not change — for example, earlier work it needs for reference. Every other tab stays hidden.
            </p>
            <div class="d-flex flex-wrap gap-2">
                @foreach (\App\Support\LeadTabs::TABS as $key => $tab)
                    <label class="jb-perm-chip">
                        <input type="checkbox" class="form-check-input m-0" name="view_modules[]" value="{{ $key }}" @checked(in_array($key, $selectedView, true))>
                        <span>{{ $tab['label'] }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error-bs :messages="$errors->get('view_modules')" />
        </div>

        <div class="jb-alert jb-alert-info mb-4">
            Saving grants this role the permissions it needs for these tabs (view / edit / generate / download). Permissions it already has are never removed — review them under Employee Roles.
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button-bs>{{ $editing ? 'Save changes' : 'Create stage' }}</x-primary-button-bs>
            <a href="{{ route('admin.workflow.index') }}" class="btn btn-secondary jb-btn-tall">Cancel</a>
        </div>
    </form>
</x-admin-bootstrap-layout>
