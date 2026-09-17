<x-app-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="text-sm font-medium text-slate-500 mb-1">
                <a href="{{ route('admin.assessment-years.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Assessment Years</a>
                <span class="mx-1">/</span>
                <span class="text-slate-900 dark:text-white">Create</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Add New Assessment Year</h1>
        </div>
        <a href="{{ route('admin.assessment-years.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg font-semibold text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
            Cancel
        </a>
    </div>

    <form method="POST" action="{{ route('admin.assessment-years.store') }}" class="space-y-6">
        @csrf
        @include('admin.assessment_years._form')

        <div class="flex justify-end gap-3">
            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-xl transition shadow-sm">
                Save Assessment Year
            </button>
        </div>
    </form>
</x-app-layout>
