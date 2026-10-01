@extends('layouts.admin')

@section('title', 'Edit '.$resource['singular'])
@section('page_title', 'Edit '.$resource['singular'])
@section('page_description', $resource['description'])

@section('page_actions')
    <a href="{{ route('admin.reference-data.'.$resourceType.'.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Back to {{ $resource['title'] }}
    </a>
@endsection

@section('content')
    <x-ui.validation-summary class="mb-3" />

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit {{ $record->name }}</h3>
        </div>
        <form method="POST" action="{{ route('admin.reference-data.'.$resourceType.'.update', $record) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="edit-name">Name</label>
                        <input id="edit-name" name="name" value="{{ old('name', $record->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label for="edit-code">Code <span class="text-muted font-weight-normal">(@if($resource['code_required']) required @else optional @endif)</span></label>
                        <input id="edit-code" name="code" value="{{ old('code', $record->code) }}" class="form-control @error('code') is-invalid @enderror" @required($resource['code_required']) @if($resource['form'] === 'nationality') maxlength="3" @endif>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if($resource['form'] === 'training-type')
                    <div class="form-group">
                        <label for="edit-description">Description</label>
                        <textarea id="edit-description" name="description" rows="4" class="form-control">{{ old('description', $record->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endif

                @if($resource['form'] === 'document-type')
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit-extensions">Allowed extensions</label>
                            <input id="edit-extensions" name="allowed_extensions" value="{{ old('allowed_extensions', implode(', ', $record->allowed_extensions ?? [])) }}" class="form-control">
                            <small class="form-text text-muted">Comma-separated values without dots.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit-mime-types">Allowed MIME types</label>
                            <input id="edit-mime-types" name="allowed_mime_types" value="{{ old('allowed_mime_types', implode(', ', $record->allowed_mime_types ?? [])) }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit-min-size">Minimum KB</label>
                            <input id="edit-min-size" type="number" name="min_size_kb" value="{{ old('min_size_kb', $record->min_size_kb) }}" class="form-control @error('min_size_kb') is-invalid @enderror" min="1" required>
                            @error('min_size_kb')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit-max-size">Maximum KB</label>
                            <input id="edit-max-size" type="number" name="max_size_kb" value="{{ old('max_size_kb', $record->max_size_kb) }}" class="form-control @error('max_size_kb') is-invalid @enderror" min="1" max="102400" required>
                            @error('max_size_kb')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="d-flex flex-wrap mb-3">
                        <div class="form-check mr-4">
                            <input id="edit-required" type="checkbox" name="is_required" value="1" class="form-check-input" @checked(old('is_required', $record->is_required))>
                            <label class="form-check-label" for="edit-required">Required for every application</label>
                        </div>
                        <div class="form-check">
                            <input id="edit-active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $record->is_active))>
                            <label class="form-check-label" for="edit-active">Active and available for selection</label>
                        </div>
                    </div>
                    @error('allowed_extensions')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    @error('allowed_mime_types')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                @endif
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-end">
                <a href="{{ route('admin.reference-data.'.$resourceType.'.index') }}" class="btn btn-light mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1" aria-hidden="true"></i> Save changes
                </button>
            </div>
        </form>
    </div>
@endsection
