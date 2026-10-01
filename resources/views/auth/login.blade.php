<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Sign in to PractiLink to manage practical training applications and placement progress.">
    <title>Sign in | PractiLink</title>

    @fonts
    @vite(['resources/css/app.css'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/practilink.css') }}" rel="stylesheet">
    <link href="{{ asset('css/public-viewport.css') }}" rel="stylesheet">
</head>
<body class="pl-auth-page">
    <div class="pl-auth-shell">
        <header class="pl-auth-nav">
            <a href="{{ route('home') }}" class="pl-auth-brand" aria-label="PractiLink home">
                <span class="pl-brand-mark">P</span>
                <span>Practi<span>Link</span></span>
            </a>
            <div class="pl-auth-nav-actions">
                <span class="d-none d-sm-inline">New student?</span>
                <a href="{{ route('register') }}" class="btn btn-sm pl-btn-outline">Create account</a>
            </div>
        </header>

        <main class="pl-auth-main">
            <section class="pl-hero-copy" aria-labelledby="login-page-title">
                <div class="pl-eyebrow"><span></span>PRACTICAL TRAINING</div>
                <h1 id="login-page-title">Welcome back to <strong>PractiLink.</strong></h1>
                <p class="pl-hero-text">Manage applications, documents, notifications and placement progress from one secure workspace.</p>
                <p class="pl-hero-text mt-3">PractiLink connects students, departments and placement teams from application through final placement.</p>
            </section>

            <aside class="pl-login-column" aria-labelledby="sign-in-title">
                <div class="pl-login-card">
                    <div class="pl-login-card-top">
                        <div class="pl-login-icon"><i class="fas fa-lock" aria-hidden="true"></i></div>
                        <div>
                            <div class="pl-login-kicker">SECURE ACCESS</div>
                            <h2 id="sign-in-title">Sign in</h2>
                        </div>
                    </div>
                    <p class="pl-login-intro">Use your account identifier and password to continue.</p>

                    @if(session('success'))
                        <div class="alert alert-success pl-alert" role="status">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger pl-alert" role="alert">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="pl-login-form">
                        @csrf
                        <div class="form-group">
                            <label for="login">Email, username or registration number</label>
                            <div class="pl-input-wrap">
                                <i class="far fa-user" aria-hidden="true"></i>
                                <input type="text" id="login" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" autocomplete="username" placeholder="Account identifier" required autofocus>
                            </div>
                            @error('login')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="pl-input-wrap pl-password-wrap">
                                <i class="fas fa-key" aria-hidden="true"></i>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" placeholder="Password" required>
                                <button type="button" class="pl-password-toggle" data-password-toggle="#password" aria-label="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <label class="pl-check mb-0">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                <span aria-hidden="true"></span> Remember me
                            </label>
                            <a href="{{ route('password.request') }}" class="small font-weight-bold">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn pl-btn-primary btn-block">
                            <span>Sign in</span><i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </form>

                    <div class="pl-login-divider"><span>OR</span></div>
                    <a href="{{ route('register') }}" class="pl-create-link">
                        <span><i class="fas fa-user-plus" aria-hidden="true"></i> Create a student account</span>
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>

                    <div class="pl-trust-row">
                        <span><i class="fas fa-shield-alt" aria-hidden="true"></i> Protected account access</span>
                        <span><i class="fas fa-clock" aria-hidden="true"></i> Activity notifications</span>
                    </div>
                </div>
            </aside>
        </main>

        <footer class="pl-auth-footer">
            <span>&copy; {{ now()->year }} PractiLink</span>
            <span>Practical training, connected.</span>
        </footer>
    </div>

    @vite('resources/js/app.js')
</body>
</html>
