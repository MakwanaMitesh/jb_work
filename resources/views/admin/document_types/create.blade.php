@extends('layouts.admin-bootstrap')

@section('title', 'Add Document Type')

@section('content')
<div class="mx-auto d-flex flex-column gap-4" style="max-width:48rem">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="jb-page-title">Add Document Type</h1>
            <p class="m-0 mt-1" style="font-size:var(--text-sm);color:var(--slate-500)">Define document rules for Lead upload tab.</p>
        </div>
        <a href="{{ route('admin.document-types.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <form action="{{ route('admin.document-types.store') }}" method="POST" class="jb-panel d-flex flex-column gap-4">
        @csrf

        <div class="row g-4">
            <div class="col-12 col-md-6">
                <x-input-label-bs for="name" value="Document Name" :required="true" />
                <x-text-input-bs id="name" type="text" name="name" :value="old('name')" placeholder="e.g. Aadhaar Card" required />
                <x-input-error-bs :messages="$errors->get('name')" />
            </div>

            <div class="col-12 col-md-6">
                <x-input-label-bs for="code" value="System Code / Key" :required="true" />
                <x-text-input-bs id="code" type="text" name="code" :value="old('code')" placeholder="e.g. aadhar_card" required />
                <x-input-error-bs :messages="$errors->get('code')" />
            </div>
        </div>

        <div>
            <x-input-label-bs for="description" value="Description / Instructions" />
            <textarea id="description" name="description" rows="2" class="jb-textarea" placeholder="Instructions displayed under upload box...">{{ old('description') }}</textarea>
            <x-input-error-bs :messages="$errors->get('description')" />
        </div>

        <div class="jb-inset-box">
          <div class="row g-3">
              <div class="col-12 col-md-4">
                  <label class="d-flex align-items-center gap-3" style="cursor:pointer">
                      <input type="checkbox" name="is_required" value="1" @checked(old('is_required')) class="form-check-input m-0">
                      <span class="fw-medium" style="font-size:var(--text-sm);color:var(--slate-800)">Is Required Document</span>
                  </label>
              </div>

              <div class="col-12 col-md-4">
                  <label class="d-flex align-items-center gap-3" style="cursor:pointer">
                      <input type="checkbox" name="has_front_back" value="1" @checked(old('has_front_back')) class="form-check-input m-0">
                      <span class="fw-medium" style="font-size:var(--text-sm);color:var(--slate-800)">Requires Front &amp; Back</span>
                  </label>
              </div>

              <div class="col-12 col-md-4">
                  <label class="d-flex align-items-center gap-3" style="cursor:pointer">
                      <input type="checkbox" name="allow_multiple" value="1" @checked(old('allow_multiple')) class="form-check-input m-0">
                      <span class="fw-medium" style="font-size:var(--text-sm);color:var(--slate-800)">Allow Multiple Files</span>
                  </label>
              </div>
          </div>
        </div>

        <div>
            <x-input-label-bs value="Allowed File Types" />
            <div class="row g-3 mt-0">
                @foreach (['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'csv'] as $ext)
                    <div class="col-6 col-sm-3">
                        <label class="jb-perm-chip w-100">
                            <input type="checkbox" name="allowed_file_types[]" value="{{ $ext }}" @checked(in_array($ext, old('allowed_file_types', ['pdf', 'jpg', 'jpeg', 'png']))) class="form-check-input m-0">
                            <span>.{{ strtoupper($ext) }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
            <x-input-error-bs :messages="$errors->get('allowed_file_types')" />
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <x-input-label-bs for="max_file_size_kb" value="Max File Size (in KB)" :required="true" />
                <x-text-input-bs id="max_file_size_kb" type="number" name="max_file_size_kb" :value="old('max_file_size_kb', 10240)" placeholder="10240 for 10MB" required />
                <p class="jb-muted mt-1 mb-0">e.g. 10240 = 10 MB, 20480 = 20 MB</p>
                <x-input-error-bs :messages="$errors->get('max_file_size_kb')" />
            </div>

            <div class="col-12 col-md-4">
                <x-input-label-bs for="sort_order" value="Sort Order" />
                <x-text-input-bs id="sort_order" type="number" name="sort_order" :value="old('sort_order', 0)" />
                <x-input-error-bs :messages="$errors->get('sort_order')" />
            </div>

            <div class="col-12 col-md-4">
                <x-input-label-bs for="status" value="Status" :required="true" />
                <select id="status" name="status" class="form-select jb-input">
                    <option value="active" @selected(old('status') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                </select>
                <x-input-error-bs :messages="$errors->get('status')" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 pt-4 border-top">
            <a href="{{ route('admin.document-types.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Document Type</button>
        </div>
    </form>
</div>
@endsection
