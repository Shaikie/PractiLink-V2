@extends('layouts.admin')
@section('title','Allocate Placement')
@section('page_title','Allocate Placement')
@section('page_description',$application->reference_number)
@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Placement details</h3></div>
    <form method="POST" action="{{ route('admin.placements.store',$application) }}">
        @csrf
        <div class="card-body">
            <div class="alert alert-light border">
                <strong>{{ $application->student->full_name }}</strong><br>
                {{ $application->student->registration_number }} · {{ $application->applicationWindow->trainingType->name }}<br>
                Approved dates: <strong>{{ $application->training_start_date->format('d M Y') }} - {{ $application->training_end_date->format('d M Y') }}</strong>
            </div>

            <div class="alert alert-info">
                <strong>Organization:</strong> The placement will be allocated to the configured PractiLink organization automatically.
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">Select department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $application->department_id) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group col-md-6">
                    <label>Supervisor</label>
                    <select name="supervisor_user_id" class="form-control">
                        <option value="">Assign later</option>
                        @foreach($supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}" @selected(old('supervisor_user_id') == $supervisor->id)>{{ $supervisor->fullname }} ({{ $supervisor->email }})</option>
                        @endforeach
                    </select>
                    @error('supervisor_user_id')<small class="text-danger">{{ $message }}</small>@enderror
                    @if($supervisors->isEmpty())
                        <small class="text-muted">No active users with the Supervisor role are available.</small>
                    @endif
                </div>

                <div class="form-group col-md-6">
                    <label>Location</label>
                    <input name="location" class="form-control" maxlength="500" value="{{ old('location') }}">
                    @error('location')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group col-md-6">
                    <label>Start date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $application->training_start_date->format('Y-m-d') }}" min="{{ $application->training_start_date->format('Y-m-d') }}" max="{{ $application->training_start_date->format('Y-m-d') }}" readonly required>
                    @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group col-md-6">
                    <label>End date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $application->training_end_date->format('Y-m-d') }}" min="{{ $application->training_end_date->format('Y-m-d') }}" max="{{ $application->training_end_date->format('Y-m-d') }}" readonly required>
                    @error('end_date')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group col-12">
                    <label>Notes</label>
                    <textarea name="notes" rows="4" class="form-control" maxlength="5000">{{ old('notes') }}</textarea>
                    @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.applications.show',$application) }}" class="btn btn-light border">Back</a>
            <button class="btn btn-primary float-right">Allocate placement</button>
        </div>
    </form>
</div>
@endsection
