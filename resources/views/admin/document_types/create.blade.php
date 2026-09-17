@extends('layouts.admin')

@section('title', 'Add Document Type')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Add Document Type</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Define document rules for Lead upload tab.</p>
        </div>
        <a href="{{ route('admin.document-types.index') }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
            Back to List
        </a>
    </div>

    <form action="{{ route('admin.document-types.store') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" value="Document Name" :required="true" />
                <x-text-input id="name" type="text" name="name" :value="old('name')" placeholder="e.g. Aadhaar Card" required />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="code" value="System Code / Key" :required="true" />
                <x-text-input id="code" type="text" name="code" :value="old('code')" placeholder="e.g. aadhar_card" required />
                <x-input-error :messages="$errors->get('code')" />
            </div>
        </div>

        <div>
            <x-input-label for="description" value="Description / Instructions" />
            <textarea id="description" name="description" rows="2" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary-500 focus:ring-primary-500/20 text-sm px-3 py-2" placeholder="Instructions displayed under upload box...">{{ old('description') }}</textarea>
            <x-input-error :messages="$errors->get('description')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-100 dark:border-slate-800">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_required" value="1" @checked(old('is_required')) class="rounded text-primary-600 focus:ring-primary-500 w-4 h-4">
                <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Is Required Document</span>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="has_front_back" value="1" @checked(old('has_front_back')) class="rounded text-primary-600 focus:ring-primary-500 w-4 h-4">
                <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Requires Front & Back</span>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="allow_multiple" value="1" @checked(old('allow_multiple')) class="rounded text-primary-600 focus:ring-primary-500 w-4 h-4">
                <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Allow Multiple Files</span>
            </label>
        </div>

        <div>
            <x-input-label value="Allowed File Types" />
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-2">
                @foreach (['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'csv'] as $ext)
                    <label class="flex items-center gap-2 p-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200/60 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                        <input type="checkbox" name="allowed_file_types[]" value="{{ $ext }}" @checked(in_array($ext, old('allowed_file_types', ['pdf', 'jpg', 'jpeg', 'png']))) class="rounded text-primary-600 focus:ring-primary-500">
                        <span>.{{ strtoupper($ext) }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('allowed_file_types')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <x-input-label for="max_file_size_kb" value="Max File Size (in KB)" :required="true" />
                <x-text-input id="max_file_size_kb" type="number" name="max_file_size_kb" :value="old('max_file_size_kb', 10240)" placeholder="10240 for 10MB" required />
                <p class="text-xs text-slate-400 mt-1">e.g. 10240 = 10 MB, 20480 = 20 MB</p>
                <x-input-error :messages="$errors->get('max_file_size_kb')" />
            </div>

            <div>
                <x-input-label for="sort_order" value="Sort Order" />
                <x-text-input id="sort_order" type="number" name="sort_order" :value="old('sort_order', 0)" />
                <x-input-error :messages="$errors->get('sort_order')" />
            </div>

            <div>
                <x-input-label for="status" value="Status" :required="true" />
                <select id="status" name="status" class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm h-10 px-3">
                    <option value="active" @selected(old('status') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
                <x-input-error :messages="$errors->get('status')" />
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('admin.document-types.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition">Save Document Type</button>
        </div>
    </form>
</div>
@endsection
