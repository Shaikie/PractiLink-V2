@extends('layouts.admin')

@section('title', 'Review '.$application->reference_number)
@section('page_title', 'Review Application')
@section('page_description', $application->reference_number)

@section('content')
    <div class="row">
        <div class="col-xl-7 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Student & application</h3></div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $application->student->full_name }}</p>
                    <p><strong>Registration:</strong> {{ $application->student->registration_number }}</p>
                    <p><strong>Email:</strong> {{ $application->student->email }}</p>
                    <p><strong>Institution:</strong> {{ $application->student->institution->name }}</p>
                    <p><strong>Course:</strong> {{ $application->student->course->name }}</p>
                    <p><strong>Study level:</strong> {{ $application->student->studyLevel->name }}</p>
                    <p><strong>Review department:</strong> {{ $application->department?->name ?? 'Not assigned' }}</p>
                    <p><strong>Current year:</strong> Year {{ $application->current_study_year }}</p>
                    <p><strong>Training:</strong> {{ $application->applicationWindow->trainingType->name }} · {{ $application->applicationWindow->name }}</p>
                    <p><strong>Training dates:</strong> {{ $application->training_start_date?->format('d M Y') }} - {{ $application->training_end_date?->format('d M Y') }}</p>
                    <hr>
                    <h6>Reason for application</h6><p>{{ $application->reason_for_application }}</p>
                    <h6>Areas of interest</h6><p>{{ $application->interests }}</p>
                    <h6>Expected objectives</h6><p>{{ $application->expected_objectives }}</p>
                    <h6>Additional notes</h6><p class="mb-0">{{ $application->notes ?: 'None' }}</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3 class="card-title">Submitted documents</h3></div>
                <div class="card-body"><div class="row">
                    @forelse($application->documents as $document)
                        <div class="col-md-6 mb-3"><div class="border rounded p-2">
                            <strong>{{ $document->documentType->name }}</strong>
                            <div class="small text-muted text-truncate">{{ $document->original_name }}</div>
                            @if($document->isPreviewable())
                                <iframe src="{{ route('admin.applications.documents.preview', [$application, $document]) }}" style="width:100%;height:220px;border:0;margin-top:8px"></iframe>
                            @else
                                <div class="py-4 text-center text-muted"><i class="far fa-file fa-2x"></i><div class="small mt-2">Preview unavailable</div></div>
                            @endif
                            <a href="{{ route('admin.applications.documents.download', [$application, $document]) }}" class="btn btn-sm btn-outline-primary btn-block mt-2">Download</a>
                        </div></div>
                    @empty
                        <div class="col-12 text-muted">No documents attached.</div>
                    @endforelse
                </div></div>
            </div>
        </div>
        <div class="col-xl-5 mb-3">
            <div class="card"><div class="card-header"><h3 class="card-title">Workflow action</h3></div><div class="card-body">
                @if($application->workflow?->currentStage)
                    <div class="alert alert-light border"><div class="small text-muted">Current stage</div><strong>{{ $application->workflow->currentStage->name }}</strong><div class="small text-muted mt-1">Application status: {{ str_replace('_', ' ', ucwords(strtolower($application->status))) }} · Workflow v{{ $application->workflow->version->version }}</div></div>
                @endif
                @if($transitions->isNotEmpty() && $application->workflow?->currentStage && ! $application->workflow->currentStage->is_terminal)
                    @foreach($transitions as $transition)
                        <form method="POST" action="{{ route('admin.applications.action', $application) }}" class="border rounded p-3 mb-2">
                            @csrf
                            <div class="font-weight-bold">{{ $transition->label }}</div>
                            <div class="small text-muted mb-2">{{ $transition->fromStage->name }} → {{ $transition->toStage->name }} · {{ $transition->result_status ? str_replace('_', ' ', ucwords(strtolower($transition->result_status))) : 'No status change' }}</div>
                            <label for="comment-{{ $transition->id }}" class="sr-only">Comment</label>
                            <textarea id="comment-{{ $transition->id }}" name="comment" class="form-control mb-2" rows="{{ $transition->requires_comment ? 3 : 2 }}" placeholder="{{ $transition->requires_comment ? 'Comment required' : 'Optional comment' }}" @if($transition->requires_comment) required @endif>{{ old('action') === $transition->action ? old('comment') : '' }}</textarea>
                            <button name="action" value="{{ $transition->action }}" class="btn btn-primary btn-block">{{ $transition->label }}</button>
                        </form>
                    @endforeach
                @else
                    <div class="alert alert-light border mb-0">This workflow stage has no available review actions.</div>
                @endif
                @if($application->status === 'ACCEPTED' && $application->workflow?->currentStage?->code === 'CTO_PLACEMENT' && ! $application->placement)
                    <a href="{{ route('admin.placements.create', $application) }}" class="btn btn-primary btn-block mt-3"><i class="fas fa-map-marker-alt mr-1"></i>Allocate placement & assign supervisor</a>
                @endif
            </div></div>
            @if($application->workflow)
                <div class="card"><div class="card-header"><h3 class="card-title">Workflow history</h3></div><div class="card-body">
                    @foreach($application->workflow->history->sortBy('acted_at') as $event)
                        <div class="mb-3"><strong>{{ $event->toStage?->name ?? 'Started' }}</strong><div class="small text-muted">{{ $event->acted_at?->format('d M Y, H:i') }}</div>@if($event->comment)<div class="small">{{ $event->comment }}</div>@endif</div>
                    @endforeach
                </div></div>
            @endif
        </div>
    </div>
@endsection
