@extends('layouts.admin')

@section('title', 'Application '.$application->reference_number)
@section('page_title', 'Application Details')
@section('page_description', $application->reference_number)

@section('content')
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title">{{ $application->applicationWindow->name }}</h3><span class="badge badge-primary">{{ str_replace('_', ' ', $application->status) }}</span></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Reference number</small><strong>{{ $application->reference_number }}</strong></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Training type</small><strong>{{ $application->applicationWindow->trainingType->name }}</strong></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Application window</small><strong>{{ $application->applicationWindow->name }}</strong></div>
                    <div class="col-md-6 mb-3"><small class="text-muted d-block">Submitted</small><strong>{{ $application->submitted_at?->format('d M Y, H:i') ?? 'Not submitted' }}</strong></div>
                </div>
                <hr>
                <div><small class="text-muted d-block mb-1">Notes</small><p class="mb-0">{{ $application->notes ?: 'No notes provided.' }}</p></div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('student.applications.index') }}" class="btn btn-light border">Back</a>
                <div>
                    @if ($application->isEditable())
                        <form method="POST" action="{{ route('student.applications.submit', $application) }}" class="d-inline">@csrf<button class="btn btn-success" type="submit">Submit application</button></form>
                    @endif
                    @if (in_array($application->status, ['DRAFT', 'SUBMITTED', 'RETURNED'], true))
                        <form method="POST" action="{{ route('student.applications.cancel', $application) }}" class="d-inline ml-1">@csrf<button class="btn btn-outline-danger" type="submit">Cancel</button></form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
