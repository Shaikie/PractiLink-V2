@extends('layouts.admin')

@section('title', 'Student Profile')
@section('page_title', 'Student Profile')
@section('page_description', 'Manage your personal and academic information.')

@section('content')
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Personal & academic details</h3></div>
            <form method="POST" action="{{ route('student.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6 form-group"><label for="first_name">First name</label><input id="first_name" type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->first_name) }}" required></div>
                        <div class="col-md-6 form-group"><label for="last_name">Last name</label><input id="last_name" type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->last_name) }}" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label for="registration_number">Registration number</label><input id="registration_number" type="text" class="form-control" value="{{ $student->registration_number }}" readonly><small class="form-text text-muted">Your registration number is your permanent student identifier.</small></div>
                        <div class="col-md-6 form-group"><label for="email">Email</label><input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $student->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label for="phone">Phone</label><input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone) }}"></div>
                        <div class="col-md-6 form-group"><label for="gender">Gender</label><select id="gender" name="gender" class="form-control" required>@foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('gender', $student->gender) === $value)>{{ $label }}</option>@endforeach</select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label for="nationality_id">Nationality</label><select id="nationality_id" name="nationality_id" class="form-control" required>@foreach ($nationalities as $nationality)<option value="{{ $nationality->id }}" @selected((string) old('nationality_id', $student->nationality_id) === (string) $nationality->id)>{{ $nationality->name }}</option>@endforeach</select></div>
                        <div class="col-md-6 form-group"><label for="institution_id">Institution</label><select id="institution_id" name="institution_id" class="form-control" required>@foreach ($institutions as $institution)<option value="{{ $institution->id }}" @selected((string) old('institution_id', $student->institution_id) === (string) $institution->id)>{{ $institution->name }}</option>@endforeach</select></div>
                    </div>
                    <div class="row mb-0">
                        <div class="col-md-6 form-group mb-md-0"><label for="course_id">Course</label><select id="course_id" name="course_id" class="form-control" required>@foreach ($courses as $course)<option value="{{ $course->id }}" @selected((string) old('course_id', $student->course_id) === (string) $course->id)>{{ $course->name }}</option>@endforeach</select></div>
                        <div class="col-md-6 form-group mb-0"><label for="study_level_id">Study level</label><select id="study_level_id" name="study_level_id" class="form-control" required>@foreach ($studyLevels as $studyLevel)<option value="{{ $studyLevel->id }}" @selected((string) old('study_level_id', $student->study_level_id) === (string) $studyLevel->id)>{{ $studyLevel->name }}</option>@endforeach</select></div>
                    </div>
                </div>
                <div class="card-footer text-right"><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save changes</button></div>
            </form>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Change password</h3></div>
            <form method="POST" action="{{ route('student.profile.password.update') }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group"><label for="current_password">Current password</label><input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="password">New password</label><input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group mb-0"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" minlength="8" required></div>
                </div>
                <div class="card-footer"><button type="submit" class="btn btn-success btn-block"><i class="fas fa-key mr-1"></i> Change password</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
