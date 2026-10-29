{{-- Expects: $permissionsByModule (Collection grouped by module), $selectedIds (array of permission ids) --}}
{{--
    jb-permission-checkbox, jb-module-toggle and jb-subsection-toggle (plus jb-subsection-checkbox) are
    JS hooks, not style classes — the script below selects on them. Keep them on these elements.

    A permission's name tells us whether it's a module-level action ("module.action", one dot) or a
    subsection within that module ("module.subsection.view", two dots) — see
    PermissionSeeder::LEAD_MODULE_SUBSECTIONS for where the latter come from. No extra data is needed
    from the controller for this; it falls straight out of the existing naming.
--}}
<div class="d-flex justify-content-end mb-2">
    <button type="button" class="btn btn-link btn-sm p-0" id="select-all-permissions">Select All</button>
    <span class="mx-2 text-muted">·</span>
    <button type="button" class="btn btn-link btn-sm p-0" id="deselect-all-permissions">Deselect All</button>
</div>

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
                @php
                    $actions = $permissions->filter(fn ($p) => substr_count($p->name, '.') === 1);
                    $subsections = $permissions->filter(fn ($p) => substr_count($p->name, '.') === 2);
                @endphp
                <tr>
                    <td class="align-top jb-cell-strong text-capitalize">{{ str_replace('_', ' ', $module) }}</td>

                    <td style="white-space:normal">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($actions as $permission)
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

                        @if ($subsections->isNotEmpty())
                            <div class="mt-2 pt-2 border-top d-flex flex-wrap align-items-center gap-2">
                                <span class="text-muted text-uppercase" style="font-size:var(--text-xs);letter-spacing:.03em">Subsections:</span>
                                @foreach ($subsections as $permission)
                                    <label for="permission-{{ $permission->id }}" class="jb-perm-chip">
                                        <input
                                            class="form-check-input jb-permission-checkbox jb-subsection-checkbox m-0"
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
                                <label class="ms-auto d-inline-flex align-items-center gap-1" style="font-size:var(--text-xs)">
                                    <input type="checkbox" class="form-check-input jb-subsection-toggle m-0" data-module="{{ $module }}">
                                    <span class="text-muted">All subsections</span>
                                </label>
                            </div>
                        @endif
                    </td>

                    <td class="align-top text-end">
                        <div class="form-check form-switch d-inline-block m-0">
                            <input type="checkbox" role="switch" class="form-check-input jb-module-toggle" data-module="{{ $module }}" id="module-toggle-{{ $module }}" title="Grants every action and subsection for this module">
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const allBoxes = () => document.querySelectorAll('.jb-permission-checkbox');

        // Module toggle: every checkbox under that module (actions + subsections).
        document.querySelectorAll('.jb-module-toggle').forEach((toggle) => {
            const module = toggle.dataset.module;
            const boxes = () => document.querySelectorAll(`.jb-permission-checkbox[data-module="${module}"]`);
            const sync = () => {
                const list = [...boxes()];
                const checkedCount = list.filter((b) => b.checked).length;
                toggle.checked = list.length > 0 && checkedCount === list.length;
                toggle.indeterminate = checkedCount > 0 && checkedCount < list.length;
            };
            sync();
            toggle.addEventListener('change', () => {
                boxes().forEach((b) => {
                    b.checked = toggle.checked;
                    b.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
            document.addEventListener('change', (e) => {
                if (e.target.dataset?.module === module) sync();
            });
        });

        // Subsection toggle: just this module's subsection checkboxes (not its plain actions).
        document.querySelectorAll('.jb-subsection-toggle').forEach((toggle) => {
            const module = toggle.dataset.module;
            const boxes = () => document.querySelectorAll(`.jb-subsection-checkbox[data-module="${module}"]`);
            const sync = () => {
                const list = [...boxes()];
                const checkedCount = list.filter((b) => b.checked).length;
                toggle.checked = list.length > 0 && checkedCount === list.length;
                toggle.indeterminate = checkedCount > 0 && checkedCount < list.length;
            };
            sync();
            toggle.addEventListener('change', () => {
                boxes().forEach((b) => {
                    b.checked = toggle.checked;
                    b.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
            document.addEventListener('change', (e) => {
                if (e.target.classList?.contains('jb-subsection-checkbox') && e.target.dataset?.module === module) sync();
            });
        });

        document.getElementById('select-all-permissions')?.addEventListener('click', () => {
            allBoxes().forEach((b) => { b.checked = true; b.dispatchEvent(new Event('change', { bubbles: true })); });
        });
        document.getElementById('deselect-all-permissions')?.addEventListener('click', () => {
            allBoxes().forEach((b) => { b.checked = false; b.dispatchEvent(new Event('change', { bubbles: true })); });
        });
    });
</script>
