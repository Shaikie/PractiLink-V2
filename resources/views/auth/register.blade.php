<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Create a PractiLink student account to apply for practical training opportunities.">
    <title>Student registration | PractiLink</title>

    @fonts
    @vite(['resources/css/app.css'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/practilink.css') }}" rel="stylesheet">
</head>
<body class="pl-auth-page pl-auth-page-scroll">
    <div class="pl-auth-shell pl-auth-form-shell">
        <header class="pl-auth-nav">
            <a href="{{ route('home') }}" class="pl-auth-brand" aria-label="PractiLink home">
                <span class="pl-brand-mark">P</span>
                <span>Practi<span>Link</span></span>
            </a>
            <div class="pl-auth-nav-actions">
                <span class="d-none d-sm-inline">Already registered?</span>
                <a href="{{ route('login') }}" class="btn btn-sm pl-btn-outline">Sign in</a>
            </div>
        </header>

        <main class="pl-auth-main pl-auth-form-main">
            <section class="pl-hero-copy" aria-labelledby="register-page-title">
                <div class="pl-eyebrow"><span></span>STUDENT ONBOARDING</div>
                <h1 id="register-page-title">Start your <strong>training journey.</strong></h1>
                <p class="pl-hero-text">Create one account to apply for opportunities, submit documents and follow every stage of your placement process.</p>

                <div class="pl-feature-grid">
                    <div class="pl-feature-item">
                        <span class="pl-feature-icon blue"><i class="fas fa-file-alt" aria-hidden="true"></i></span>
                        <div><strong>One application</strong><small>Keep every training window and submission organized.</small></div>
                    </div>
                    <div class="pl-feature-item">
                        <span class="pl-feature-icon green"><i class="fas fa-folder-open" aria-hidden="true"></i></span>
                        <div><strong>Secure documents</strong><small>Upload private files for authorized reviewers only.</small></div>
                    </div>
                    <div class="pl-feature-item">
                        <span class="pl-feature-icon blue"><i class="fas fa-bell" aria-hidden="true"></i></span>
                        <div><strong>Clear updates</strong><small>Receive timely status and placement notifications.</small></div>
                    </div>
                    <div class="pl-feature-item">
                        <span class="pl-feature-icon green"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                        <div><strong>Visible progress</strong><small>Follow review stages from draft to placement.</small></div>
                    </div>
                </div>
            </section>

            <aside class="pl-login-column pl-register-column" aria-labelledby="registration-title">
                <div class="pl-login-card">
                    <div class="pl-login-card-top">
                        <div class="pl-login-icon"><i class="fas fa-user-plus" aria-hidden="true"></i></div>
                        <div>
                            <div class="pl-login-kicker">STUDENT ACCOUNT</div>
                            <h2 id="registration-title">Create your account</h2>
                        </div>
                    </div>
                    <p class="pl-login-intro">Complete your profile accurately. You can review these details before submitting an application.</p>

                    @if($errors->any())
                        <div class="alert alert-danger pl-alert" role="alert">
                            <strong class="d-block mb-1">Please correct the following:</strong>
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="pl-login-form pl-register-form" novalidate>
                        @csrf

                        <fieldset class="pl-form-section">
                            <legend><i class="fas fa-id-card" aria-hidden="true"></i> Personal information</legend>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="first_name">First name</label>
                                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" class="form-control @error('first_name') is-invalid @enderror" autocomplete="given-name" required autofocus>
                                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="last_name">Last name</label>
                                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" class="form-control @error('last_name') is-invalid @enderror" autocomplete="family-name" required>
                                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="registration_number">Registration number</label>
                                    <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number') }}" class="form-control @error('registration_number') is-invalid @enderror" required>
                                    @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="gender">Gender</label>
                                    <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                        <option value="">Select gender</option>
                                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                                    </select>
                                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email">Email address</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="phone">Phone number <span class="text-muted font-weight-normal">(optional)</span></label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" autocomplete="tel">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-12">
                                    <label for="nationality_id">Nationality</label>
                                    <select id="nationality_id" name="nationality_id" class="form-control @error('nationality_id') is-invalid @enderror" required>
                                        <option value="">Select nationality</option>
                                        @foreach($nationalities as $nationality)
                                            <option value="{{ $nationality->id }}" @selected(old('nationality_id') == $nationality->id)>{{ $nationality->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('nationality_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="pl-form-section">
                            <legend><i class="fas fa-graduation-cap" aria-hidden="true"></i> Academic information</legend>
                            <div class="form-group">
                                <label for="institution_id">Institution</label>
                                <select id="institution_id" name="institution_id" class="form-control @error('institution_id') is-invalid @enderror" required>
                                    <option value="">Select institution</option>
                                    @foreach($institutions as $institution)
                                        <option value="{{ $institution->id }}" @selected(old('institution_id') == $institution->id)>{{ $institution->name }}</option>
                                    @endforeach
                                </select>
                                @error('institution_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-7">
                                    <label for="course_id">Course</label>
                                    <select id="course_id" name="course_id" class="form-control @error('course_id') is-invalid @enderror" required>
                                        <option value="">Select course</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->name }} ({{ $course->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="study_level_id">Study level</label>
                                    <select id="study_level_id" name="study_level_id" class="form-control @error('study_level_id') is-invalid @enderror" required>
                                        <option value="">Select level</option>
                                        @foreach($studyLevels as $studyLevel)
                                            <option value="{{ $studyLevel->id }}" @selected(old('study_level_id') == $studyLevel->id)>{{ $studyLevel->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('study_level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="pl-form-section">
                            <legend><i class="fas fa-lock" aria-hidden="true"></i> Account security</legend>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="password">Password</label>
                                    <div class="pl-input-wrap pl-password-wrap">
                                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                                        <button type="button" class="pl-password-toggle" data-password-toggle="#password" aria-label="Show password"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                    </div>
                                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="password_confirmation">Confirm password</label>
                                    <div class="pl-input-wrap pl-password-wrap">
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="8" required>
                                        <button type="button" class="pl-password-toggle" data-password-toggle="#password_confirmation" aria-label="Show password"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">Use at least eight characters with upper and lowercase letters and a number.</small>
                        </fieldset>

                        <button type="submit" class="btn pl-btn-primary btn-block mt-2">
                            <span>Create student account</span><i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </aside>
        </main>

        <footer class="pl-auth-footer">
            <span>&copy; {{ now()->year }} PractiLink</span>
            <span>Your information is handled securely.</span>
        </footer>
    </div>

    @vite('resources/js/app.js')
</body>
</html>
