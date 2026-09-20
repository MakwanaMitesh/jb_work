{{-- Expects: $permissionsByModule (Collection grouped by module), $selectedIds (array of permission ids) --}}
{{--
    jb-permission-checkbox and jb-module-toggle are JS hooks, not style classes
    — the script below selects on them. Keep them on these elements.
--}}
<div class="table-responsive rounded-3 border">
    <table class="jb-table jb-table-compact bg-white">
        <thead>
            <tr>
                <th>Module</th>
                <th>Granted Permissions</th>
                <th class="text-end" style="width:7rem">Toggle All</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permissionsByModule as $module => $permissions)
                <tr>
                    <td class="align-top jb-cell-strong text-capitalize">{{ str_replace('_', ' ', $module) }}</td>

                    <td style="white-space:normal">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($permissions as $permission)
                                <label for="permission-{{ $permission->id }}" class="jb-perm-chip">
                                    <input
                                        class="form-check-input jb-permission-checkbox m-0"
                                        data-module="{{ $module }}"
                                        type="checkbox"
                                        name="permissions[]"
                                        id="permission-{{ $permission->id }}"
                                        value="{{ $permission->id }}"
                                        @checked(in_array($permission->id, $selectedIds))
                                    >
                                    <span>{{ $permission->description ?? $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </td>

                    <td class="align-top text-end">
                        <div class="form-check form-switch d-inline-block m-0">
                            <input type="checkbox" role="switch" class="form-check-input jb-module-toggle" data-module="{{ $module }}" id="module-toggle-{{ $module }}">
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.jb-module-toggle').forEach((toggle) => {
            const module = toggle.dataset.module;
            const boxes = document.querySelectorAll(`.jb-permission-checkbox[data-module="${module}"]`);
            toggle.checked = boxes.length > 0 && [...boxes].every((b) => b.checked);
            toggle.addEventListener('change', () => {
                boxes.forEach((b) => {
                    b.checked = toggle.checked;
                    // Trigger native change event to update parent container state styles
                    b.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        });
    });
</script>
