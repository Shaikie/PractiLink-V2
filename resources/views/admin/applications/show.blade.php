@extends('layouts.admin')

@section('title', 'Review '.$application->reference_number)
@section('page_title', 'Review application')
@section('page_description', $application->reference_number.' · '.$application->student->full_name)

@section('page_actions')
    <a href="{{ route('admin.applications.index') }}" class="btn btn-light">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Review queue
    </a>
@endsection

@section('content')
    <x-ui.validation-summary class="mb-3" />

    <div class="row">
        <div class="col-xl-8 mb-3">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <span class="account-avatar account-avatar-lg" aria-hidden="true">{{ \Illuminate\Support\Str::of($application->student->full_name)->explode(' ')->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->implode('') }}</span>
                        <div>
                            <h3 class="card-title">{{ $application->student->full_name }}</h3>
                            <p class="mb-0 mt-1 text-muted small">{{ $application->student->registration_number }} · {{ $application->student->email }}</p>
                        </div>
                    </div>
                    <x-ui.status-badge :status="$application->status" class="ml-auto" />
                </div>
                <div class="card-body">
                    <dl class="pl-detail-grid">
                        <div class="pl-detail-item"><dt>Institution</dt><dd>{{ $application->student->institution->name }}</dd></div>
                        <div class="pl-detail-item"><dt>Course</dt><dd>{{ $application->student->course->name }}</dd></div>
                        <div class="pl-detail-item"><dt>Study level</dt><dd>{{ $application->student->studyLevel->name }}</dd></div>
                        <div class="pl-detail-item"><dt>Current year</dt><dd>Year {{ $application->current_study_year }}</dd></div>
                        <div class="pl-detail-item"><dt>Review department</dt><dd>{{ $application->department?->name ?? 'Not assigned' }}</dd></div>
                        <div class="pl-detail-item"><dt>Training</dt><dd>{{ $application->applicationWindow->trainingType->name }}</dd></div>
                        <div class="pl-detail-item"><dt>Window</dt><dd>{{ $application->applicationWindow->name }}</dd></div>
                        <div class="pl-detail-item"><dt>Training dates</dt><dd>{{ $application->training_start_date?->format('d M Y') }} – {{ $application->training_end_date?->format('d M Y') }}</dd></div>
                    </dl>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Application narrative</h3></div>
                <div class="card-body pl-section-copy">
                    <h6>Reason for application</h6>
                    <p>{{ \Illuminate\Support\Str::of($application->reason_for_application ?: 'Not provided.')->stripTags()->toString() }}</p>
                    <h6>Areas of interest</h6>
                    <p>{{ \Illuminate\Support\Str::of($application->interests ?: 'Not provided.')->stripTags()->toString() }}</p>
                    <h6>Expected objectives</h6>
                    <p>{{ \Illuminate\Support\Str::of($application->expected_objectives ?: 'Not provided.')->stripTags()->toString() }}</p>
                    <h6>Additional notes</h6>
                    <p>{{ \Illuminate\Support\Str::of($application->notes ?: 'No additional notes were provided.')->stripTags()->toString() }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Submitted documents</h3>
                        <p class="mb-0 mt-1 text-muted small">Only authorized staff can preview or download these files.</p>
                    </div>
                    <span class="badge badge-light ml-auto">{{ $application->documents->count() }} files</span>
                </div>
                <div class="card-body">
                    @if($application->documents->isEmpty())
                        <x-ui.empty-state
                            icon="fa-folder-open"
                            title="No documents attached"
                            message="The student has not uploaded supporting documents for this application."
                            compact
                        />
                    @else
                        <div class="pl-document-grid">
                            @foreach($application->documents as $document)
                                <article class="pl-document-card">
                                    <div class="pl-document-heading">
                                        <span class="pl-document-icon"><i class="far fa-file-alt" aria-hidden="true"></i></span>
                                        <div class="pl-document-copy">
                                            <strong>{{ $document->documentType->name }}</strong>
                                            <small title="{{ $document->original_name }}">{{ $document->original_name }}</small>
                                            <small>{{ number_format($document->size_bytes / 1024, 1) }} KB</small>
                                        </div>
                                    </div>

                                    @if($document->isPreviewable())
                                        <iframe class="pl-document-preview" src="{{ route('admin.applications.documents.preview', [$application, $document]) }}" title="Preview of {{ $document->documentType->name }}" loading="lazy"></iframe>
                                    @else
                                        <div class="pl-document-placeholder">
                                            <i class="far fa-file-word fa-2x" aria-hidden="true"></i>
                                            <span class="mt-2">Preview is unavailable for this file type.</span>
                                        </div>
                                    @endif

                                    <div class="pl-document-actions">
                                        <a href="{{ route('admin.applications.documents.download', [$application, $document]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download" aria-hidden="true"></i> Download
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-3">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Workflow action</h3></div>
                <div class="card-body">
                    @if($application->workflow?->currentStage)
                        <div class="pl-next-callout mb-3">
                            <span><i class="fas fa-project-diagram" aria-hidden="true"></i></span>
                            <div>
                                <div class="small text-uppercase font-weight-bold">Current stage</div>
                                <strong class="d-block mt-1">{{ $application->workflow->currentStage->name }}</strong>
                                <p>Workflow version {{ $application->workflow->version->version }}</p>
                            </div>
                        </div>
                    @endif

                    @if($transitions->isNotEmpty() && $application->workflow?->currentStage && ! $application->workflow->currentStage->is_terminal)
                        @foreach($transitions as $transition)
                            <form method="POST" action="{{ route('admin.applications.action', $application) }}" class="workflow-action-card" data-confirm="Apply the “{{ $transition->label }}” action to this application?">
                                @csrf
                                <div class="workflow-action-heading">
                                    <div>
                                        <strong>{{ $transition->label }}</strong>
                                        <small>{{ $transition->fromStage->name }} → {{ $transition->toStage->name }}</small>
                                    </div>
                                    @if($transition->result_status)
                                        <x-ui.status-badge :status="$transition->result_status" />
                                    @endif
                                </div>
                                <label for="comment-{{ $transition->id }}" class="sr-only">Comment for {{ $transition->label }}</label>
                                <textarea
                                    id="comment-{{ $transition->id }}"
                                    name="comment"
                                    class="form-control mb-2 @error('comment') is-invalid @enderror"
                                    rows="{{ $transition->requires_comment ? 3 : 2 }}"
                                    placeholder="{{ $transition->requires_comment ? 'A comment is required for this action' : 'Add an optional review comment' }}"
                                    @required($transition->requires_comment)
                                >{{ old('action') === $transition->action ? old('comment') : '' }}</textarea>
                                @error('comment')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                                <button name="action" value="{{ $transition->action }}" class="btn btn-primary btn-block" type="submit">
                                    {{ $transition->label }} <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
                                </button>
                            </form>
                        @endforeach
                    @else
                        <div class="alert alert-light border mb-0">
                            <i class="fas fa-circle-info mr-2" aria-hidden="true"></i>
                            No workflow actions are available at this stage.
                        </div>
                    @endif

                    @if($application->status === 'ACCEPTED' && $application->workflow?->currentStage?->code === 'CTO_PLACEMENT' && ! $application->placement)
                        <a href="{{ route('admin.placements.create', $application) }}" class="btn btn-success btn-block mt-3">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i> Allocate placement
                        </a>
                    @endif
                </div>
            </div>

            @if($application->workflow)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Review timeline</h3></div>
                    <div class="card-body">
                        <x-ui.workflow-timeline :events="$application->workflow->history ?? collect()" />
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
