<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach ($permissionsByModule as $module => $permissions)
        <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-slate-50/20 dark:bg-slate-900/10">
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 dark:border-slate-800 pb-2">
                <h6 class="text-sm font-bold text-slate-800 dark:text-slate-200 text-capitalize mb-0">{{ str_replace('_', ' ', $module) }}</h6>
                <div class="flex items-center gap-1.5">
                    <input class="rounded border-slate-300 dark:border-slate-700 text-primary-600 focus:ring-primary-500/20 jb-module-toggle w-4 h-4" type="checkbox" data-module="{{ $module }}" id="module-toggle-{{ $module }}">
                    <label class="text-xs text-slate-500 dark:text-slate-400 font-medium cursor-pointer" for="module-toggle-{{ $module }}">All</label>
                </div>
            </div>
            <div class="space-y-2">
                @foreach ($permissions as $permission)
                    <div class="flex items-center gap-2">
                        <input
                            class="rounded border-slate-300 dark:border-slate-700 text-primary-600 focus:ring-primary-500/20 jb-permission-checkbox w-4.5 h-4.5 cursor-pointer"
                            data-module="{{ $module }}"
                            type="checkbox"
                            name="permissions[]"
                            id="permission-{{ $permission->id }}"
                            value="{{ $permission->id }}"
                            @checked(in_array($permission->id, $selectedIds))
                        >
                        <label class="text-sm text-slate-600 dark:text-slate-400 cursor-pointer" for="permission-{{ $permission->id }}">
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
