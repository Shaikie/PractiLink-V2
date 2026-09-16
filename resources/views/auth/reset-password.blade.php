<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset password | PractiLink</title>
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
            <div class="pl-eyebrow"><span></span>SECURE ACCESS</div>
            <h1>Create a <strong>new password.</strong></h1>
            <p class="pl-hero-text">Choose a strong password for your {{ $accountType === 'student' ? 'student' : 'staff / admin' }} account.</p>
        </section>
        <aside class="pl-login-column">
            <div class="pl-login-card">
                <div class="pl-login-card-top"><div class="pl-login-icon"><i class="fas fa-lock"></i></div><div><div class="pl-login-kicker">PASSWORD RESET</div><h2>New password</h2></div></div>
                @if($errors->any())<div class="alert alert-danger pl-alert" role="alert"><ul class="mb-0 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('password.update') }}" class="pl-login-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="account_type" value="{{ $accountType }}">
                    <div class="form-group"><label for="email">Email address</label><div class="pl-input-wrap"><i class="far fa-envelope"></i><input type="email" id="email" name="email" value="{{ old('email',$email) }}" class="form-control" autocomplete="email" required></div></div>
                    <div class="form-group"><label for="password">New password</label><div class="pl-input-wrap"><i class="fas fa-key"></i><input type="password" id="password" name="password" class="form-control" autocomplete="new-password" minlength="8" required></div><small class="form-text text-muted">Use at least 8 characters with letters, mixed case and numbers.</small></div>
                    <div class="form-group"><label for="password_confirmation">Confirm password</label><div class="pl-input-wrap"><i class="fas fa-check"></i><input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="8" required></div></div>
                    <button type="submit" class="btn pl-btn-primary btn-block"><span>Reset password</span><i class="fas fa-arrow-right"></i></button>
                </form>
            </div>
        </aside>
    </main>
    <footer class="pl-auth-footer"><span>© {{ date('Y') }} PractiLink</span><span>Practical training, connected.</span></footer>
</div>
</body>
</html>
