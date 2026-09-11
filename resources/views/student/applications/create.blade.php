@extends('layouts.admin')
@section('title','Start Application')
@section('page_title','Start New Application')
@section('page_description','Choose an open training window and save your application as a draft before submitting it.')
@section('content')
<div class="clay-page-narrow">
@if($errors->any())<div class="alert alert-danger clay-card border-0 mb-3"><strong>Please correct the following:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@forelse($windows as $window)
<form method="POST" action="{{ route('student.applications.store') }}" class="clay-card mb-4">
@csrf
<input type="hidden" name="application_window_id" value="{{ $window->id }}">
<div class="clay-card-body">
<div class="clay-section-heading"><span class="clay-icon"><i class="fas fa-file-alt"></i></span><div><h3>{{ $window->name }}</h3><p>{{ $window->trainingType->name }} · application window closes {{ $window->closes_at->format('d M Y, H:i') }}</p></div></div>
<div class="form-row"><div class="form-group col-md-8"><label>Reason for application</label><textarea name="reason_for_application" class="form-control" rows="4" minlength="20" maxlength="5000" placeholder="Explain why you are applying for this practical training opportunity." required>{{ old('reason_for_application') }}</textarea></div><div class="form-group col-md-4"><label>Current year of study</label><select name="current_study_year" class="custom-select" required><option value="">Select year</option>@for($year=1;$year<=8;$year++)<option value="{{ $year }}" @selected(old('current_study_year')==$year)>Year {{ $year }}</option>@endfor</select></div></div>
<div class="form-group"><label>Areas of interest</label><textarea name="interests" class="form-control" rows="4" minlength="10" maxlength="5000" placeholder="Describe the technical or professional areas you want to gain experience in." required>{{ old('interests') }}</textarea></div>
<div class="form-group"><label>Expected objectives</label><textarea name="expected_objectives" class="form-control" rows="4" minlength="20" maxlength="5000" placeholder="What do you expect to learn, contribute, or accomplish during the training?" required>{{ old('expected_objectives') }}</textarea></div>
<div class="form-row"><div class="form-group col-md-6"><label>Training start</label><input type="date" name="training_start_date" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ old('training_start_date') }}" required></div><div class="form-group col-md-6"><label>Training end</label><input type="date" name="training_end_date" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ old('training_end_date') }}" required></div></div>
<div class="form-group mb-0"><label>Additional notes <span class="font-weight-normal clay-muted">(optional)</span></label><textarea name="notes" class="form-control" rows="3" maxlength="5000" placeholder="Add anything else the placement team should know.">{{ old('notes') }}</textarea></div>
</div>
<div class="clay-card-footer"><a href="{{ route('student.applications.index') }}" class="btn btn-light clay-btn-secondary">Cancel</a><button class="btn btn-primary clay-btn-primary"><i class="fas fa-save mr-1"></i>Save Draft</button></div>
</form>
@empty
<div class="clay-card clay-empty-state"><div class="clay-empty-icon"><i class="fas fa-calendar-times"></i></div><h3>No open application windows</h3><p>There are currently no training windows accepting applications.</p><a href="{{ route('student.applications.index') }}" class="btn btn-light clay-btn-secondary">Back to Applications</a></div>
@endforelse
</div>
@endsection
