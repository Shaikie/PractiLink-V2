@extends('layouts.admin')

@section('title', 'Application '.$application->reference_number)
@section('page_title', 'Application details')
@section('page_description', $application->reference_number)

@section('page_actions')
    <div class="d-flex flex-wrap justify-content-end gap-2">
        @if($application->isEditable())
            <a href="{{ route('student.applications.edit', $application) }}" class="btn btn-primary">
                <i class="fas fa-pen" aria-hidden="true"></i> Edit application
            </a>
        @endif
        <a href="{{ route('student.applications.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> All applications
        </a>
    </div>
@endsection

@section('content')
    <x-ui.validation-summary class="mb-3" />

    @php
        $nextStep = match ($application->status) {
            'DRAFT' => 'Complete the required information and documents, then submit when ready.',
            'RETURNED' => 'Review the workflow comments, update your application and submit it again.',
            'SUBMITTED', 'UNDER_REVIEW' => 'Your application is with the review team. We will notify you when it moves.',
            'ACCEPTED' => $application->placement ? 'Your placement is ready. Open the placement details below.' : 'Your application was accepted. The placement team will allocate an organization next.',
            'REJECTED' => 'This application was not approved. Contact the department if you need clarification.',
            'CANCELLED' => 'This application is closed and no longer requires action.',
            default => 'Continue completing your application details.',
        };
    @endphp

    <div class="row">
        <div class="col-xl-8 mb-3">
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">{{ $application->applicationWindow->name }}</h3>
                        <p class="mb-0 mt-1 text-muted small">Submitted {{ $application->submitted_at?->format('d M Y, H:i') ?? 'not yet' }}</p>
                    </div>
                    <x-ui.status-badge :status="$application->status" class="ml-auto" />
                </div>
                <div class="card-body">
                    <div class="pl-next-callout mb-4">
                        <span><i class="fas fa-compass" aria-hidden="true"></i></span>
                        <div>
                            <strong>What happens next</strong>
                            <p>{{ $nextStep }}</p>
                        </div>
                    </div>

                    <dl class="pl-detail-grid mb-0">
                        <div class="pl-detail-item">
                            <dt>Reference number</dt>
                            <dd>{{ $application->reference_number }}</dd>
                        </div>
                        <div class="pl-detail-item">
                            <dt>Training type</dt>
                            <dd>{{ $application->applicationWindow->trainingType->name }}</dd>
                        </div>
                        <div class="pl-detail-item">
                            <dt>Training starts</dt>
                            <dd>{{ $application->training_start_date?->format('d M Y') ?? 'Not set' }}</dd>
                        </div>
                        <div class="pl-detail-item">
                            <dt>Training ends</dt>
                            <dd>{{ $application->training_end_date?->format('d M Y') ?? 'Not set' }}</dd>
                        </div>
                        <div class="pl-detail-item">
                            <dt>Review department</dt>
                            <dd>{{ $application->department?->name ?? 'Not assigned' }}</dd>
                        </div>
                        <div class="pl-detail-item">
                            <dt>Current workflow stage</dt>
                            <dd>{{ $application->workflow?->currentStage?->name ?? 'Not started' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="card-footer pl-placement-actions">
                    <a href="{{ route('student.applications.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Back
                    </a>
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        @if(in_array($application->status, ['DRAFT', 'SUBMITTED', 'RETURNED'], true))
                            <form method="POST" action="{{ route('student.applications.cancel', $application) }}" data-confirm="Cancel this application? This action cannot be undone.">
                                @csrf
                                <button class="btn btn-outline-danger" type="submit">Cancel application</button>
                            </form>
                        @endif
                        @if($application->isEditable())
                            <form method="POST" action="{{ route('student.applications.submit', $application) }}" data-confirm="Submit this application for review? You will not be able to edit it unless it is returned.">
                                @csrf
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-paper-plane" aria-hidden="true"></i> Submit application
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Application narrative</h3>
                        <p class="mb-0 mt-1 text-muted small">Information submitted with this application</p>
                    </div>
                </div>
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
                        <h3 class="card-title">Application documents</h3>
                        <p class="mb-0 mt-1 text-muted small">PDF, JPG, PNG and Word files up to the configured size limit</p>
                    </div>
                    <span class="badge badge-light ml-auto">{{ $application->documents->count() }} uploaded</span>
                </div>
                <div class="card-body">
                    @if($application->isEditable())
                        <form method="POST" action="{{ route('student.applications.documents.store', $application) }}" enctype="multipart/form-data" class="pl-document-upload mb-4">
                            @csrf
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-4 mb-md-0">
                                    <label for="document_type_id">Document type</label>
                                    <select id="document_type_id" name="document_type_id" class="form-control" required>
                                        <option value="">Select document</option>
                                        @foreach($documentTypes as $type)
                                            <option value="{{ $type->id }}" @selected(old('document_type_id') == $type->id)>
                                                {{ $type->name }}{{ $type->is_required ? ' (required)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-5 mb-md-0">
                                    <label for="document">File</label>
                                    <input type="file" id="document" name="document" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                </div>
                                <div class="form-group col-md-3 mb-md-0">
                                    <button class="btn btn-primary btn-block" type="submit">
                                        <i class="fas fa-cloud-arrow-up" aria-hidden="true"></i> Upload document
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted d-block mt-2">The server validates the actual file content before storing it privately.</small>
                        </form>
                    @endif

                    @if($application->documents->isEmpty())
                        <x-ui.empty-state
                            icon="fa-folder-open"
                            title="No documents uploaded"
                            message="Upload the required supporting documents before submitting this application."
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
                                        <iframe class="pl-document-preview" src="{{ route('student.applications.documents.preview', [$application, $document]) }}" title="Preview of {{ $document->documentType->name }}" loading="lazy"></iframe>
                                    @else
                                        <div class="pl-document-placeholder">
                                            <i class="far fa-file-word fa-2x" aria-hidden="true"></i>
                                            <span class="mt-2">Preview is unavailable for this file type.</span>
                                        </div>
                                    @endif

                                    <div class="pl-document-actions">
                                        <a href="{{ route('student.applications.documents.download', [$application, $document]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download" aria-hidden="true"></i> Download
                                        </a>
                                        @if($application->isEditable())
                                            <form method="POST" action="{{ route('student.applications.documents.destroy', [$application, $document]) }}" data-confirm="Remove this document?">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                            </form>
                                        @endif
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
                <div class="card-header"><h3 class="card-title">Document checklist</h3></div>
                <div class="card-body py-2">
                    @foreach($documentTypes as $type)
                        @php($isUploaded = $application->documents->contains('document_type_id', $type->id))
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <span class="small">{{ $type->name }}</span>
                            <x-ui.status-badge
                                :status="$isUploaded ? 'Uploaded' : ($type->is_required ? 'Required' : 'Optional')"
                                :tone="$isUploaded ? 'success' : ($type->is_required ? 'warning' : 'neutral')"
                            />
                        </div>
                    @endforeach
                    <p class="small text-muted mb-0 mt-3">All required documents must be uploaded before submission.</p>
                </div>
            </div>

            @if($application->placement)
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Placement details</h3></div>
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="pl-document-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                            <div>
                                <strong class="d-block">{{ $application->placement->organization->name }}</strong>
                                <span class="small text-muted">{{ $application->placement->location ?: 'Location available from the placement office' }}</span>
                            </div>
                        </div>
                        <dl class="pl-detail-grid mb-3">
                            <div class="pl-detail-item"><dt>Starts</dt><dd>{{ $application->placement->start_date->format('d M Y') }}</dd></div>
                            <div class="pl-detail-item"><dt>Ends</dt><dd>{{ $application->placement->end_date->format('d M Y') }}</dd></div>
                        </dl>
                        <x-ui.status-badge :status="$application->placement->status" />
                        @if($application->placement->letters->isNotEmpty())
                            <a href="{{ route('student.placements.letter', $application->placement->letters->sortByDesc('version')->first()) }}" target="_blank" rel="noopener" class="btn btn-primary btn-block mt-3">
                                <i class="fas fa-file-pdf" aria-hidden="true"></i> View placement letter
                            </a>
                        @endif
                    </div>
                </div>
            @endif

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
