{{-- Expects: $permissionsByModule (Collection grouped by module), $selectedIds (array of permission ids) --}}
<div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
    <table class="w-full text-left border-collapse bg-white dark:bg-slate-900">
        <thead>
            <tr class="bg-slate-50/75 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                <th class="px-6 py-4">Module</th>
                <th class="px-6 py-4">Granted Permissions</th>
                <th class="px-6 py-4 text-right w-28">Toggle All</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($permissionsByModule as $module => $permissions)
                <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/10 transition">
                    <!-- Module Name -->
                    <td class="px-6 py-4.5 whitespace-nowrap align-top">
                        <span class="font-bold text-slate-800 dark:text-slate-200 text-capitalize text-sm">{{ str_replace('_', ' ', $module) }}</span>
                    </td>

                    <!-- Permission Options Grid -->
                    <td class="px-6 py-4.5">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($permissions as $permission)
                                <label for="permission-{{ $permission->id }}" 
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/50 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition cursor-pointer select-none text-xs font-semibold text-slate-700 dark:text-slate-300 has-[:checked]:border-primary-500 dark:has-[:checked]:border-primary-400 has-[:checked]:bg-primary-50/40 dark:has-[:checked]:bg-primary-950/20">
                                    <input
                                        class="rounded border border-slate-200 dark:border-slate-700 text-primary-600 focus:ring-primary-500/20 jb-permission-checkbox w-4 h-4 cursor-pointer"
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

                    <!-- Toggle All Switch -->
                    <td class="px-6 py-4.5 align-top text-right">
                        <div class="inline-flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" class="sr-only peer jb-module-toggle" data-module="{{ $module }}" id="module-toggle-{{ $module }}">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary-600"></div>
                            </label>
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
