@extends('layouts.admin')

@section('title', 'Student Profile')
@section('page_title', 'Student Profile')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">Academic & Personal Details</h3>
        </div>
        <form method="POST" action="{{ route('student.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="first_name">First name</label>
                        <input id="first_name" type="text" name="first_name" class="form-control" value="{{ old('first_name', $student->first_name) }}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="last_name">Last name</label>
                        <input id="last_name" type="text" name="last_name" class="form-control" value="{{ old('last_name', $student->last_name) }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="registration_number">Registration number</label>
                        <input id="registration_number" type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $student->registration_number) }}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control" required>
                            @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('gender', $student->gender) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="nationality_id">Nationality</label>
                        <select id="nationality_id" name="nationality_id" class="form-control" required>
                            @foreach ($nationalities as $nationality)
                                <option value="{{ $nationality->id }}" @selected((string) old('nationality_id', $student->nationality_id) === (string) $nationality->id)>{{ $nationality->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="institution_id">Institution</label>
                        <select id="institution_id" name="institution_id" class="form-control" required>
                            @foreach ($institutions as $institution)
                                <option value="{{ $institution->id }}" @selected((string) old('institution_id', $student->institution_id) === (string) $institution->id)>{{ $institution->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-0">
                    <div class="col-md-6 form-group mb-md-0">
                        <label for="course_id">Course</label>
                        <select id="course_id" name="course_id" class="form-control" required>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected((string) old('course_id', $student->course_id) === (string) $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <label for="study_level_id">Study level</label>
                        <select id="study_level_id" name="study_level_id" class="form-control" required>
                            @foreach ($studyLevels as $studyLevel)
                                <option value="{{ $studyLevel->id }}" @selected((string) old('study_level_id', $student->study_level_id) === (string) $studyLevel->id)>{{ $studyLevel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('profile.edit') }}" class="btn btn-secondary">Back to Profile</a>
                <button type="submit" class="btn btn-info">Save Student Details</button>
            </div>
        </form>
    </div>
@endsection
