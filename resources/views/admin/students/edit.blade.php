@extends('layouts.admin')

@section('title', 'Edit '.$student->full_name)
@section('page_title', 'Edit student')
@section('page_description', $student->registration_number.' · '.$student->full_name)

@section('page_actions')
    <a href="{{ route('admin.students.index') }}" class="btn btn-light"><i class="fas fa-arrow-left" aria-hidden="true"></i> Student directory</a>
@endsection

@section('content')
    <x-ui.validation-summary class="mb-3" />

    <div class="row">
        <div class="col-xl-8 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Student information</h3></div>
                <form method="POST" action="{{ route('admin.students.update', $student) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="first_name">First name</label>
                                <input id="first_name" name="first_name" value="{{ old('first_name', $student->first_name) }}" class="form-control @error('first_name') is-invalid @enderror" required>
                                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="last_name">Last name</label>
                                <input id="last_name" name="last_name" value="{{ old('last_name', $student->last_name) }}" class="form-control @error('last_name') is-invalid @enderror" required>
                                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="registration_number">Registration number</label>
                                <input id="registration_number" name="registration_number" value="{{ old('registration_number', $student->registration_number) }}" class="form-control @error('registration_number') is-invalid @enderror" required>
                                @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email address</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $student->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="phone">Phone</label>
                                <input id="phone" name="phone" value="{{ old('phone', $student->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror">
                                    <option value="">Not specified</option>
                                    @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('gender', $student->gender) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="nationality_id">Nationality</label>
                                <select id="nationality_id" name="nationality_id" class="form-control @error('nationality_id') is-invalid @enderror">
                                    <option value="">Not specified</option>
                                    @foreach($nationalities as $nationality)
                                        <option value="{{ $nationality->id }}" @selected((string) old('nationality_id', $student->nationality_id) === (string) $nationality->id)>{{ $nationality->name }}</option>
                                    @endforeach
                                </select>
                                @error('nationality_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="institution_id">Institution</label>
                                <select id="institution_id" name="institution_id" class="form-control @error('institution_id') is-invalid @enderror" required>
                                    @foreach($institutions as $institution)
                                        <option value="{{ $institution->id }}" @selected((string) old('institution_id', $student->institution_id) === (string) $institution->id)>{{ $institution->name }}</option>
                                    @endforeach
                                </select>
                                @error('institution_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="course_id">Course</label>
                                <select id="course_id" name="course_id" class="form-control @error('course_id') is-invalid @enderror" required>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" @selected((string) old('course_id', $student->course_id) === (string) $course->id)>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                                @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="study_level_id">Study level</label>
                                <select id="study_level_id" name="study_level_id" class="form-control @error('study_level_id') is-invalid @enderror" required>
                                    @foreach($studyLevels as $studyLevel)
                                        <option value="{{ $studyLevel->id }}" @selected((string) old('study_level_id', $student->study_level_id) === (string) $studyLevel->id)>{{ $studyLevel->name }}</option>
                                    @endforeach
                                </select>
                                @error('study_level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-check mt-2">
                            <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $student->is_active))>
                            <label class="form-check-label" for="is_active">Allow this student to sign in</label>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> Save student</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-4 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Account overview</h3></div>
                <div class="card-body">
                    <dl class="pl-detail-grid mb-3">
                        <div class="pl-detail-item"><dt>Status</dt><dd><x-ui.status-badge :status="$student->is_active ? 'Active' : 'Inactive'" :tone="$student->is_active ? 'success' : 'neutral'" /></dd></div>
                        <div class="pl-detail-item"><dt>Last login</dt><dd>{{ $student->last_login_at?->format('d M Y, H:i') ?? 'Never' }}</dd></div>
                        <div class="pl-detail-item"><dt>Created</dt><dd>{{ $student->created_at?->format('d M Y') }}</dd></div>
                        <div class="pl-detail-item"><dt>Applications</dt><dd>{{ $student->applications_count }}</dd></div>
                    </dl>
                    <p class="small text-muted mb-0">Use the account switch to suspend access without deleting the student’s application history.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
