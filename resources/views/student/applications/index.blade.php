@extends('layouts.admin')

@section('title', 'My Applications')
@section('page_title', 'My Applications')
@section('page_description', 'Create, complete and track your practical training applications.')

@section('content')
<div class="row">
    <div class="col-xl-8 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">My applications</h3></div>
            <div class="card-body p-0">
                @if ($applications->isEmpty())
                    <div class="p-4 text-center text-muted"><i class="fas fa-folder-open fa-2x mb-3"></i><p class="mb-0">You have no applications yet.</p></div>
                @else
                    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Reference</th><th>Training</th><th>Study year</th><th>Status</th><th></th></tr></thead><tbody>
                    @foreach ($applications as $application)
                        <tr>
                            <td class="font-weight-bold">{{ $application->reference_number }}</td>
                            <td>{{ $application->applicationWindow->trainingType->name }}</td>
                            <td>{{ $application->current_study_year ? 'Year '.$application->current_study_year : '—' }}</td>
                            <td><span class="badge badge-{{ $application->status === 'ACCEPTED' ? 'success' : ($application->status === 'REJECTED' ? 'danger' : ($application->status === 'RETURNED' ? 'warning' : 'primary')) }}">{{ str_replace('_',' ',$application->status) }}</span></td>
                            <td class="text-right"><a href="{{ route('student.applications.show',$application) }}" class="btn btn-sm btn-outline-primary">{{ $application->isEditable() ? 'Continue' : 'View' }}</a></td>
                        </tr>
                    @endforeach
                    </tbody></table></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-4 mb-3">
        <div class="card"><div class="card-header"><h3 class="card-title">Start a draft</h3></div><div class="card-body">
            @forelse ($windows as $window)
                <div class="border rounded p-3 mb-3">
                    <div class="font-weight-bold">{{ $window->name }}</div>
                    <div class="small text-muted mb-3">{{ $window->trainingType->name }} · closes {{ $window->closes_at->format('d M Y, H:i') }}</div>
                    <form method="POST" action="{{ route('student.applications.store') }}">@csrf
                        <input type="hidden" name="application_window_id" value="{{ $window->id }}">
                        <div class="form-group"><label class="small font-weight-bold">Reason for application</label><textarea name="reason_for_application" class="form-control form-control-sm" rows="3" maxlength="10000" required>{{ old('reason_for_application') }}</textarea></div>
                        <div class="form-group"><label class="small font-weight-bold">Areas of interest</label><textarea name="interests" class="form-control form-control-sm" rows="3" maxlength="10000" required>{{ old('interests') }}</textarea></div>
                        <div class="form-group"><label class="small font-weight-bold">Expected objectives</label><textarea name="expected_objectives" class="form-control form-control-sm" rows="3" maxlength="10000" required>{{ old('expected_objectives') }}</textarea></div>
                        <div class="form-group"><label class="small font-weight-bold">Current study year</label><select name="current_study_year" class="form-control form-control-sm" required><option value="">Select year</option>@for($year=1;$year<=8;$year++)<option value="{{ $year }}" @selected(old('current_study_year')==$year)>Year {{ $year }}</option>@endfor</select></div>
                        <div class="form-group"><label class="small font-weight-bold">Training start date</label><input type="date" name="training_start_date" class="form-control form-control-sm" min="{{ now()->format('Y-m-d') }}" value="{{ old('training_start_date') }}" required></div>
                        <div class="form-group mb-3"><label class="small font-weight-bold">Training end date</label><input type="date" name="training_end_date" class="form-control form-control-sm" min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ old('training_end_date') }}" required></div>
                        <button class="btn btn-primary btn-block" type="submit">Create / save draft</button>
                    </form>
                </div>
            @empty
                <p class="text-muted mb-0">There are no open application windows right now.</p>
            @endforelse
        </div></div>
    </div>
</div>
@endsection
