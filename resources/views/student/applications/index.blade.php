@extends('layouts.admin')
@section('title', 'My Applications')
@section('page_title', 'My Applications')
@section('page_description', 'Create, complete and track your practical training applications.')
@section('content')
<div class="row">
    <div class="col-12 mb-3">
        @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
        @if($errors->any()) <div class="alert alert-danger"><strong>Please correct the following:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    </div>
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">My applications</h3></div>
            <div class="card-body p-0">
                @if($applications->isEmpty())
                    <div class="p-4 text-center text-muted"><i class="fas fa-folder-open fa-2x mb-3"></i><p class="mb-0">You have no applications yet.</p></div>
                @else
                    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Reference</th><th>Training</th><th>Study year</th><th>Dates</th><th>Status</th><th></th></tr></thead><tbody>
                    @foreach($applications as $application)
                        <tr><td class="font-weight-bold">{{ $application->reference_number }}</td><td>{{ $application->applicationWindow->trainingType->name }}</td><td>{{ $application->current_study_year ? 'Year '.$application->current_study_year : '—' }}</td><td>{{ $application->training_start_date?->format('d M Y') }} - {{ $application->training_end_date?->format('d M Y') }}</td><td><span class="badge badge-{{ in_array($application->status,['ACCEPTED','COMPLETED']) ? 'success' : ($application->status === 'REJECTED' ? 'danger' : 'primary') }}">{{ str_replace('_',' ',$application->status) }}</span></td><td class="text-right"><a href="{{ route('student.applications.show',$application) }}" class="btn btn-sm btn-outline-primary">View</a></td></tr>
                    @endforeach
                    </tbody></table></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card"><div class="card-header"><h3 class="card-title">Start a draft</h3></div><div class="card-body">
            <p class="small text-muted">Choose an open training window. You will complete and save the application before submitting it for review.</p>
            @forelse($windows as $window)
                <div class="border rounded p-3 mb-3">
                    <div class="font-weight-bold">{{ $window->name }}</div>
                    <div class="small text-muted mb-3">{{ $window->trainingType->name }} · closes {{ $window->closes_at->format('d M Y, H:i') }}</div>
                    <form method="POST" action="{{ route('student.applications.store') }}">@csrf
                        <input type="hidden" name="application_window_id" value="{{ $window->id }}">
                        <div class="form-group"><label>Reason for application</label><textarea name="reason_for_application" class="form-control" rows="3" minlength="20" maxlength="5000" required>{{ old('reason_for_application') }}</textarea></div>
                        <div class="form-group"><label>Areas of interest</label><textarea name="interests" class="form-control" rows="3" minlength="10" maxlength="5000" required>{{ old('interests') }}</textarea></div>
                        <div class="form-group"><label>Expected objectives</label><textarea name="expected_objectives" class="form-control" rows="3" minlength="20" maxlength="5000" required>{{ old('expected_objectives') }}</textarea></div>
                        <div class="form-group"><label>Current year of study</label><select name="current_study_year" class="form-control" required><option value="">Select year</option>@for($year=1;$year<=8;$year++)<option value="{{ $year }}" @selected(old('current_study_year')==$year)>Year {{ $year }}</option>@endfor</select></div>
                        <div class="form-row"><div class="form-group col-6"><label>Training start</label><input type="date" name="training_start_date" class="form-control" min="{{ now()->format('Y-m-d') }}" value="{{ old('training_start_date') }}" required></div><div class="form-group col-6"><label>Training end</label><input type="date" name="training_end_date" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ old('training_end_date') }}" required></div></div>
                        <div class="form-group"><label>Additional notes <span class="text-muted font-weight-normal">(optional)</span></label><textarea name="notes" class="form-control" rows="2" maxlength="5000">{{ old('notes') }}</textarea></div>
                        <button class="btn btn-primary btn-block" type="submit">Save draft</button>
                    </form>
                </div>
            @empty <p class="text-muted mb-0">There are no open application windows right now.</p> @endforelse
        </div></div>
    </div>
</div>
@endsection
