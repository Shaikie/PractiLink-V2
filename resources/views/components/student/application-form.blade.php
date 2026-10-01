@props([
    'action' => '',
    'application' => null,
    'cancelRoute' => '',
    'departments' => [],
    'method' => 'POST',
    'submitLabel' => 'Save',
    'windows' => [],
])

@php
    $isEditing = $application !== null;
    $applicationWindowId = old('application_window_id', $application?->application_window_id);
@endphp

<form method="POST" action="{{ $action }}" class="card">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="card-header">
        <div>
            <h3 class="card-title">{{ $isEditing ? 'Update application details' : 'Application details' }}</h3>
            <p class="mb-0 mt-1 text-muted small">Fields marked with an asterisk are required before submission.</p>
        </div>
    </div>

    <div class="card-body">
        @if($isEditing)
            <input type="hidden" name="application_window_id" value="{{ $applicationWindowId }}">
            <div class="alert alert-light border d-flex align-items-center gap-2 mb-4">
                <i class="fas fa-calendar-alt text-primary" aria-hidden="true"></i>
                <span><strong>{{ $application->applicationWindow->name }}</strong> · {{ $application->applicationWindow->trainingType->name }}</span>
            </div>
        @else
            <div class="form-group">
                <label for="application_window_id">Application window <span class="text-danger">*</span></label>
                <select id="application_window_id" name="application_window_id" class="form-control @error('application_window_id') is-invalid @enderror" required>
                    <option value="">Select an open window</option>
                    @foreach($windows as $window)
                        <option value="{{ $window->id }}" @selected((string) $applicationWindowId === (string) $window->id)>
                            {{ $window->name }} · {{ $window->trainingType->name }} · closes {{ $window->closes_at->format('d M Y') }}
                        </option>
                    @endforeach
                </select>
                @error('application_window_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @endif

        <div class="form-group">
            <label for="department_id">Department for review <span class="text-danger">*</span></label>
            <select id="department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                <option value="">Select department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) old('department_id', $application?->department_id) === (string) $department->id)>
                        {{ $department->name }}{{ $department->code ? ' ('.$department->code.')' : '' }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Your application will be routed to the department responsible for review.</small>
            @error('department_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="current_study_year">Current year of study <span class="text-danger">*</span></label>
                <select id="current_study_year" name="current_study_year" class="form-control @error('current_study_year') is-invalid @enderror" required>
                    <option value="">Select year</option>
                    @for($year = 1; $year <= 20; $year++)
                        <option value="{{ $year }}" @selected((string) old('current_study_year', $application?->current_study_year) === (string) $year)>Year {{ $year }}</option>
                    @endfor
                </select>
                @error('current_study_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-7 date-range-group">
                <label for="training_start_date">Training dates <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" id="training_start_date" name="training_start_date" value="{{ old('training_start_date', $application?->training_start_date?->format('Y-m-d')) }}" class="form-control @error('training_start_date') is-invalid @enderror" data-date-start data-min-date="{{ now()->format('Y-m-d') }}" placeholder="Start date" autocomplete="off" required>
                    <div class="input-group-append"><span class="input-group-text">to</span></div>
                    <input type="text" id="training_end_date" name="training_end_date" value="{{ old('training_end_date', $application?->training_end_date?->format('Y-m-d')) }}" class="form-control @error('training_end_date') is-invalid @enderror" data-date-end data-min-date="{{ now()->addDay()->format('Y-m-d') }}" placeholder="End date" autocomplete="off" required>
                </div>
                <small class="form-text text-muted">Choose dates within the selected training period.</small>
                @error('training_start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('training_end_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="reason_for_application">Reason for application <span class="text-danger">*</span></label>
            <textarea id="reason_for_application" name="reason_for_application" class="form-control tinymce-editor @error('reason_for_application') is-invalid @enderror" rows="4" minlength="20" maxlength="5000" placeholder="Explain why you are applying for this practical training opportunity." required>{{ old('reason_for_application', $application?->reason_for_application) }}</textarea>
            <small class="form-text text-muted">Use at least 20 characters. Rich-text formatting is supported.</small>
            @error('reason_for_application')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="interests">Areas of interest <span class="text-danger">*</span></label>
            <textarea id="interests" name="interests" class="form-control tinymce-editor @error('interests') is-invalid @enderror" rows="4" minlength="10" maxlength="5000" placeholder="Describe the technical or professional areas you want to gain experience in." required>{{ old('interests', $application?->interests) }}</textarea>
            @error('interests')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="expected_objectives">Expected objectives <span class="text-danger">*</span></label>
            <textarea id="expected_objectives" name="expected_objectives" class="form-control tinymce-editor @error('expected_objectives') is-invalid @enderror" rows="4" minlength="20" maxlength="5000" placeholder="What do you expect to learn, contribute, or accomplish during the training?" required>{{ old('expected_objectives', $application?->expected_objectives) }}</textarea>
            @error('expected_objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group mb-0">
            <label for="notes">Additional notes <span class="text-muted font-weight-normal">(optional)</span></label>
            <textarea id="notes" name="notes" class="form-control tinymce-editor" rows="3" maxlength="5000" placeholder="Add anything else the placement team should know.">{{ old('notes', $application?->notes) }}</textarea>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ $cancelRoute }}" class="btn btn-light">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas {{ $isEditing ? 'fa-save' : 'fa-file-alt' }}" aria-hidden="true"></i> {{ $submitLabel }}
        </button>
    </div>
</form>
