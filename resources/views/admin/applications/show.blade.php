@extends('layouts.admin')

@section('title', 'Review '.$application->reference_number)
@section('page_title', 'Review Application')
@section('page_description', $application->reference_number)

@section('content')
<div class="row">
    <div class="col-lg-7 mb-3"><div class="card"><div class="card-header"><h3 class="card-title">Student information</h3></div><div class="card-body">
        <p><strong>Name:</strong> {{ $application->student->full_name }}</p><p><strong>Registration:</strong> {{ $application->student->registration_number }}</p><p><strong>Email:</strong> {{ $application->student->email }}</p><p><strong>Institution:</strong> {{ $application->student->institution->name }}</p><p><strong>Course:</strong> {{ $application->student->course->name }}</p><p class="mb-0"><strong>Study level:</strong> {{ $application->student->studyLevel->name }}</p>
        <hr><p class="mb-0"><strong>Training:</strong> {{ $application->applicationWindow->trainingType->name }} · {{ $application->applicationWindow->name }}</p>
    </div></div></div>
    <div class="col-lg-5 mb-3"><div class="card"><div class="card-header"><h3 class="card-title">Update status</h3></div><form method="POST" action="{{ route('admin.applications.status.update', $application) }}">@csrf @method('PUT')<div class="card-body">
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="form-group"><label for="status">Status</label><select id="status" name="status" class="form-control" required>@foreach(['UNDER_REVIEW'=>'Under review','RETURNED'=>'Returned','ACCEPTED'=>'Accepted','REJECTED'=>'Rejected'] as $value=>$label)<option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="form-group mb-0"><label for="notes">Review notes</label><textarea id="notes" name="notes" rows="5" class="form-control" maxlength="5000">{{ old('notes', $application->notes) }}</textarea></div>
    </div><div class="card-footer"><button class="btn btn-primary btn-block">Save status</button></div></form></div></div>
</div>
@endsection
