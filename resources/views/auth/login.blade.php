<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="PractiLink connects students with practical training opportunities and streamlines applications, review and placement. ">
    <title>PractiLink | Practical Training, Connected</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/practilink.css') }}" rel="stylesheet">
</head>
<body class="pl-auth-page">
    <div class="pl-auth-shell">
        <header class="pl-auth-nav">
            <a href="{{ route('login') }}" class="pl-auth-brand" aria-label="PractiLink home">
                <span class="pl-brand-mark">P</span>
                <span>Practi<span>Link</span></span>
            </a>
            <div class="pl-auth-nav-actions">
                <span class="d-none d-sm-inline">New student?</span>
                <a href="{{ route('register') }}" class="btn btn-sm pl-btn-outline">Create account</a>
            </div>
        </header>

        <main class="pl-auth-main">
            <section class="pl-hero-copy">
                <div class="pl-eyebrow"><span></span> PRACTICAL TRAINING PLATFORM</div>
                <h1>Turn practical training into a <strong>connected journey.</strong></h1>
                <p class="pl-hero-text">
                    PractiLink brings students, institutions and training stakeholders into one structured platform for applications, review, communication and progress tracking.
                </p>

                <div class="pl-feature-grid">
                    <div class="pl-feature-item">
                        <span class="pl-feature-icon blue"><i class="fas fa-file-alt"></i></span>
                        <div><strong>Apply with confidence</strong><small>Submit and manage practical training applications in one place.</small></div>
                    </div>
                    <div class="pl-feature-item">
                        <span class="pl-feature-icon green"><i class="fas fa-route"></i></span>
                        <div><strong>Follow every step</strong><small>Track application progress from submission through review.</small></div>
                    </div>
                    <div class="pl-feature-item">
                        <span class="pl-feature-icon blue"><i class="fas fa-bell"></i></span>
                        <div><strong>Stay informed</strong><small>Receive timely status updates and important notifications.</small></div>
                    </div>
                    <div class="pl-feature-item">
                        <span class="pl-feature-icon green"><i class="fas fa-shield-alt"></i></span>
                        <div><strong>One secure platform</strong><small>Separate student and staff accounts with a unified sign-in experience.</small></div>
                    </div>
                </div>

                <div class="pl-process">
                    <div class="pl-process-heading">HOW PRACTILINK WORKS</div>
                    <div class="pl-process-steps">
                        <div class="pl-process-step"><span>01</span><div><strong>Create</strong><small>Student account</small></div></div>
                        <i class="fas fa-chevron-right"></i>
                        <div class="pl-process-step"><span>02</span><div><strong>Apply</strong><small>Training opportunity</small></div></div>
                        <i class="fas fa-chevron-right"></i>
                        <div class="pl-process-step"><span>03</span><div><strong>Track</strong><small>Application progress</small></div></div>
                    </div>
                </div>
            </section>

            <aside class="pl-login-column">
                <div class="pl-login-card">
                    <div class="pl-login-card-top">
                        <div class="pl-login-icon"><i class="fas fa-lock"></i></div>
                        <div>
                            <div class="pl-login-kicker">SECURE ACCESS</div>
                            <h2>Welcome back</h2>
                        </div>
                    </div>
                    <p class="pl-login-intro">Sign in to continue to your PractiLink workspace.</p>

                    @if(session('success'))
                        <div class="alert alert-success pl-alert" role="alert"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger pl-alert" role="alert">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="pl-login-form">
                        @csrf
                        <div class="form-group">
                            <label for="login">Email, username or registration number</label>
                            <div class="pl-input-wrap">
                                <i class="far fa-user"></i>
                                <input type="text" id="login" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" autocomplete="username" placeholder="Enter your account identifier" required autofocus>
                            </div>
                            @error('login')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password">Password</label>
                            </div>
                            <div class="pl-input-wrap pl-password-wrap">
                                <i class="fas fa-key"></i>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" placeholder="Enter your password" required>
                                <button type="button" class="pl-password-toggle" id="togglePassword" aria-label="Show password"><i class="fas fa-eye"></i></button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <label class="pl-check mb-0"><input type="checkbox" id="remember" name="remember" value="1"><span></span> Remember me</label>
                        </div>

                        <button type="submit" class="btn pl-btn-primary btn-block">
                            <span>Sign in to PractiLink</span><i class="fas fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="pl-login-divider"><span>OR</span></div>
                    <a href="{{ route('register') }}" class="pl-create-link"><span><i class="fas fa-user-plus"></i> Create a student account</span><i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="pl-trust-row">
                    <span><i class="fas fa-shield-alt"></i> Secure authentication</span>
                    <span><i class="fas fa-user-graduate"></i> Student & staff access</span>
                </div>
            </aside>
        </main>

        <footer class="pl-auth-footer">
            <span>© {{ date('Y') }} PractiLink</span>
            <span>Practical training, connected.</span>
        </footer>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            icon.classList.toggle('fa-eye', visible);
            icon.classList.toggle('fa-eye-slash', !visible);
            this.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        });
    </script>
</body>
</html>
