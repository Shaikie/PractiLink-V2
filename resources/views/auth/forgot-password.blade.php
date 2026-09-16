<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot password | PractiLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/practilink.css') }}" rel="stylesheet">
    <link href="{{ asset('css/public-viewport.css') }}" rel="stylesheet">
    <link href="{{ asset('css/skeuomorphism.css') }}" rel="stylesheet">
</head>
<body class="pl-auth-page">
<div class="pl-auth-shell">
    <header class="pl-auth-nav">
        <a href="{{ route('home') }}" class="pl-auth-brand"><span class="pl-brand-mark">P</span><span>Practi<span>Link</span></span></a>
        <div class="pl-auth-nav-actions"><a href="{{ route('login') }}" class="btn btn-sm pl-btn-outline">Back to sign in</a></div>
    </header>
    <main class="pl-auth-main">
        <section class="pl-hero-copy">
            <div class="pl-eyebrow"><span></span>ACCOUNT RECOVERY</div>
            <h1>Reset your <strong>PractiLink</strong> password.</h1>
            <p class="pl-hero-text">Enter the email address linked to your account. We will send you a secure password reset link.</p>
        </section>
        <aside class="pl-login-column">
            <div class="pl-login-card">
                <div class="pl-login-card-top"><div class="pl-login-icon"><i class="fas fa-key"></i></div><div><div class="pl-login-kicker">PASSWORD RESET</div><h2>Forgot password?</h2></div></div>
                @if(session('status'))<div class="alert alert-success pl-alert" role="alert">{{ session('status') }}</div>@endif
                @if($errors->any())<div class="alert alert-danger pl-alert" role="alert"><ul class="mb-0 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('password.email') }}" class="pl-login-form">
                    @csrf
                    <div class="form-group">
                        <label for="account_type">Account type</label>
                        <select id="account_type" name="account_type" class="form-control" required>
                            <option value="student" @selected(old('account_type','student')==='student')>Student</option>
                            <option value="staff" @selected(old('account_type')==='staff')>Staff / Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <div class="pl-input-wrap"><i class="far fa-envelope"></i><input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" placeholder="you@example.com" required autofocus></div>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn pl-btn-primary btn-block"><span>Send reset link</span><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </aside>
    </main>
    <footer class="pl-auth-footer"><span>© {{ date('Y') }} PractiLink</span><span>Practical training, connected.</span></footer>
</div>
</body>
</html>
