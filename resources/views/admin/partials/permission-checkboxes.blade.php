{{-- Expects: $permissionsByModule (Collection grouped by module), $selectedIds (array of permission ids) --}}
<div class="row g-3">
    @foreach ($permissionsByModule as $module => $permissions)
        <div class="col-md-6">
            <div class="border rounded-3 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-semibold text-capitalize mb-0">{{ str_replace('_', ' ', $module) }}</h6>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input jb-module-toggle" type="checkbox" data-module="{{ $module }}">
                        <label class="form-check-label small text-muted">All</label>
                    </div>
                </div>
                @foreach ($permissions as $permission)
                    <div class="form-check">
                        <input
                            class="form-check-input jb-permission-checkbox"
                            data-module="{{ $module }}"
                            type="checkbox"
                            name="permissions[]"
                            id="permission-{{ $permission->id }}"
                            value="{{ $permission->id }}"
                            @checked(in_array($permission->id, $selectedIds))
                        >
                        <label class="form-check-label small" for="permission-{{ $permission->id }}">
                            {{ $permission->description ?? $permission->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.jb-module-toggle').forEach((toggle) => {
            const module = toggle.dataset.module;
            const boxes = document.querySelectorAll(`.jb-permission-checkbox[data-module="${module}"]`);
            toggle.checked = boxes.length > 0 && [...boxes].every((b) => b.checked);
            toggle.addEventListener('change', () => {
                boxes.forEach((b) => (b.checked = toggle.checked));
            });
        });
    });
</script>
