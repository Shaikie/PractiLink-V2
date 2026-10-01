@extends('layouts.admin')

@section('title', $resource['title'])
@section('page_title', $resource['title'])
@section('page_description', $resource['description'])

@section('content')
    <x-ui.validation-summary class="mb-3" />

    <div class="row">
        <div class="col-xl-4 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Add {{ $resource['singular'] }}</h3></div>
                <form method="POST" action="{{ route('admin.reference-data.'.$resourceType.'.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="new-name">Name</label>
                            <input id="new-name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="new-code">Code <span class="text-muted font-weight-normal">(@if($resource['code_required']) required @else optional @endif)</span></label>
                            <input id="new-code" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" @required($resource['code_required']) @if($resource['form'] === 'nationality') maxlength="3" @endif>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if($resource['form'] === 'training-type')
                            <div class="form-group mb-0">
                                <label for="new-description">Description</label>
                                <textarea id="new-description" name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                            </div>
                        @endif

                        @if($resource['form'] === 'document-type')
                            <div class="form-group">
                                <label for="new-extensions">Allowed extensions</label>
                                <input id="new-extensions" name="allowed_extensions" value="{{ old('allowed_extensions') }}" class="form-control" placeholder="pdf, jpg, png">
                                <small class="form-text text-muted">Comma-separated values without dots.</small>
                            </div>
                            <div class="form-group">
                                <label for="new-mime-types">Allowed MIME types</label>
                                <input id="new-mime-types" name="allowed_mime_types" value="{{ old('allowed_mime_types') }}" class="form-control" placeholder="application/pdf, image/jpeg">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label for="new-min-size">Minimum KB</label>
                                    <input id="new-min-size" type="number" name="min_size_kb" value="{{ old('min_size_kb', 1) }}" class="form-control" min="1" required>
                                </div>
                                <div class="form-group col-6">
                                    <label for="new-max-size">Maximum KB</label>
                                    <input id="new-max-size" type="number" name="max_size_kb" value="{{ old('max_size_kb', 5120) }}" class="form-control" min="1" max="102400" required>
                                </div>
                            </div>
                            <div class="form-check mb-2">
                                <input id="new-required" type="checkbox" name="is_required" value="1" class="form-check-input" @checked(old('is_required'))>
                                <label class="form-check-label" for="new-required">Required for every application</label>
                            </div>
                            <div class="form-check">
                                <input id="new-active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', true))>
                                <label class="form-check-label" for="new-active">Active and available for selection</label>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus" aria-hidden="true"></i> Add {{ $resource['singular'] }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-8 mb-3">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Configured records</h3>
                        <p class="mb-0 mt-1 text-muted small">{{ $records->total() }} {{ \Illuminate\Support\Str::plural($resource['singular'], $records->total()) }}</p>
                    </div>
                </div>
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.reference-data.'.$resourceType.'.index') }}" class="pl-inline-search">
                        <div class="pl-input-wrap flex-grow-1">
                            <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                            <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name or code">
                        </div>
                        <button class="btn btn-primary" type="submit">Search</button>
                        @if($search)<a href="{{ route('admin.reference-data.'.$resourceType.'.index') }}" class="btn btn-light">Clear</a>@endif
                    </form>
                </div>

                @if($records->isEmpty())
                    <div class="card-body">
                        <x-ui.empty-state
                            icon="fa-database"
                            title="No records configured"
                            message="Add the first {{ $resource['singular'] }} using the form beside this list."
                            compact
                        />
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table clay-table mb-0">
                            <thead>
                                <tr>
                                    @foreach($resource['columns'] as $label)
                                        <th>{{ $label }}</th>
                                    @endforeach
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $record)
                                    <tr>
                                        @foreach($resource['columns'] as $key => $label)
                                            <td>
                                                @if($key === 'description')
                                                    {{ \Illuminate\Support\Str::limit($record->description ?: '—', 80) }}
                                                @elseif($key === 'is_required')
                                                    <x-ui.status-badge :status="$record->is_required ? 'Required' : 'Optional'" :tone="$record->is_required ? 'warning' : 'neutral'" />
                                                @elseif($key === 'is_active')
                                                    <x-ui.status-badge :status="$record->is_active ? 'Active' : 'Inactive'" :tone="$record->is_active ? 'success' : 'neutral'" />
                                                @else
                                                    {{ $record->{$key} ?: '—' }}
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-right">
                                            <button class="btn btn-sm btn-light" type="button" data-toggle="collapse" data-target="#edit-{{ $resourceType }}-{{ $record->id }}" aria-expanded="false">Edit</button>
                                        </td>
                                    </tr>
                                    <tr class="collapse" id="edit-{{ $resourceType }}-{{ $record->id }}">
                                        <td colspan="{{ count($resource['columns']) + 1 }}">
                                            <form method="POST" action="{{ route('admin.reference-data.'.$resourceType.'.update', $record) }}" class="pl-inline-edit-form">
                                                @csrf
                                                @method('PUT')
                                                <div class="form-row">
                                                    <div class="form-group col-md-4">
                                                        <label for="edit-name-{{ $record->id }}">Name</label>
                                                        <input id="edit-name-{{ $record->id }}" name="name" value="{{ old('name', $record->name) }}" class="form-control" required>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="edit-code-{{ $record->id }}">Code</label>
                                                        <input id="edit-code-{{ $record->id }}" name="code" value="{{ old('code', $record->code) }}" class="form-control" @required($resource['code_required']) @if($resource['form'] === 'nationality') maxlength="3" @endif>
                                                    </div>
                                                    @if($resource['form'] === 'training-type')
                                                        <div class="form-group col-md-5">
                                                            <label for="edit-description-{{ $record->id }}">Description</label>
                                                            <input id="edit-description-{{ $record->id }}" name="description" value="{{ old('description', $record->description) }}" class="form-control">
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($resource['form'] === 'document-type')
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4">
                                                            <label for="edit-extensions-{{ $record->id }}">Allowed extensions</label>
                                                            <input id="edit-extensions-{{ $record->id }}" name="allowed_extensions" value="{{ old('allowed_extensions', implode(', ', $record->allowed_extensions ?? [])) }}" class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label for="edit-mime-{{ $record->id }}">Allowed MIME types</label>
                                                            <input id="edit-mime-{{ $record->id }}" name="allowed_mime_types" value="{{ old('allowed_mime_types', implode(', ', $record->allowed_mime_types ?? [])) }}" class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label for="edit-min-{{ $record->id }}">Min KB</label>
                                                            <input id="edit-min-{{ $record->id }}" type="number" name="min_size_kb" value="{{ old('min_size_kb', $record->min_size_kb) }}" class="form-control" min="1" required>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label for="edit-max-{{ $record->id }}">Max KB</label>
                                                            <input id="edit-max-{{ $record->id }}" type="number" name="max_size_kb" value="{{ old('max_size_kb', $record->max_size_kb) }}" class="form-control" min="1" max="102400" required>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                                        <div class="form-check"><input id="edit-required-{{ $record->id }}" type="checkbox" name="is_required" value="1" class="form-check-input" @checked($record->is_required)><label class="form-check-label" for="edit-required-{{ $record->id }}">Required</label></div>
                                                        <div class="form-check"><input id="edit-active-{{ $record->id }}" type="checkbox" name="is_active" value="1" class="form-check-input" @checked($record->is_active)><label class="form-check-label" for="edit-active-{{ $record->id }}">Active</label></div>
                                                    </div>
                                                @endif
                                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save" aria-hidden="true"></i> Save changes</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($records->hasPages())
                        <div class="px-3 py-3 border-top">{{ $records->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
