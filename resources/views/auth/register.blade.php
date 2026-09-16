<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | PractiLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container col-md-8 offset-md-2 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h2>Student Registration</h2>
                        <p class="text-muted mb-0">Create your PractiLink student account.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <h5 class="border-bottom pb-2 mb-3">Personal Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" class="form-control @error('first_name') is-invalid @enderror" required autofocus>
                                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" class="form-control @error('last_name') is-invalid @enderror" required>
                                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="registration_number" class="form-label">Registration Number</label>
                                <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number') }}" class="form-control @error('registration_number') is-invalid @enderror" required>
                                @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select gender</option>
                                    <option value="male" @selected(old('gender') === 'male')>Male</option>
                                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                                    <option value="other" @selected(old('gender') === 'other')>Other</option>
                                </select>
                                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nationality_id" class="form-label">Nationality</label>
                                <select id="nationality_id" name="nationality_id" class="form-select @error('nationality_id') is-invalid @enderror" required>
                                    <option value="">Select nationality</option>
                                    @foreach ($nationalities as $nationality)
                                        <option value="{{ $nationality->id }}" @selected(old('nationality_id') == $nationality->id)>{{ $nationality->name }}</option>
                                    @endforeach
                                </select>
                                @error('nationality_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3 mt-3">Academic Information</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="institution_id" class="form-label">Institution</label>
                                <select id="institution_id" name="institution_id" class="form-select @error('institution_id') is-invalid @enderror" required>
                                    <option value="">Select institution</option>
                                    @foreach ($institutions as $institution)
                                        <option value="{{ $institution->id }}" @selected(old('institution_id') == $institution->id)>{{ $institution->name }}</option>
                                    @endforeach
                                </select>
                                @error('institution_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="course_id" class="form-label">Course</label>
                                <select id="course_id" name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                    <option value="">Select course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->name }} ({{ $course->code }})</option>
                                    @endforeach
                                </select>
                                @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="study_level_id" class="form-label">Study Level</label>
                                <select id="study_level_id" name="study_level_id" class="form-select @error('study_level_id') is-invalid @enderror" required>
                                    <option value="">Select study level</option>
                                    @foreach ($studyLevels as $studyLevel)
                                        <option value="{{ $studyLevel->id }}" @selected(old('study_level_id') == $studyLevel->id)>{{ $studyLevel->name }}</option>
                                    @endforeach
                                </select>
                                @error('study_level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3 mt-3">Account Security</h5>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Create Student Account</button>
                    </form>

                    <div class="text-center mt-3">
                        Already have an account? <a href="{{ route('login') }}">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
