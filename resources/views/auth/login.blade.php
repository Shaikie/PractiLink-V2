<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in | PractiLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css" rel="stylesheet"><link href="{{ asset('css/practilink.css') }}" rel="stylesheet">
    <style>body{min-height:100vh;background:#f8fafc}.auth-shell{min-height:100vh}.auth-brand{color:var(--pl-blue-900);font-weight:800;font-size:2rem}.auth-mark{display:inline-flex;width:42px;height:42px;align-items:center;justify-content:center;border-radius:12px;background:var(--pl-green-600);color:#fff;margin-right:8px}.auth-card{border:1px solid var(--pl-slate-200);border-radius:16px;box-shadow:0 18px 45px rgba(15,23,42,.08)}.auth-heading{font-weight:750;color:var(--pl-slate-900)}.auth-subtitle{color:var(--pl-slate-500)}.password-toggle{cursor:pointer}</style>
</head>
<body><div class="container auth-shell d-flex align-items-center justify-content-center py-5"><div class="row w-100 justify-content-center"><div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">
<div class="text-center mb-4"><div class="auth-brand"><span class="auth-mark">P</span>PractiLink</div><p class="auth-subtitle mt-2 mb-0">Practical training, connected.</p></div>
<div class="card auth-card border-0"><div class="card-body p-4 p-md-5"><div class="mb-4"><h1 class="h4 auth-heading mb-1">Welcome back</h1><p class="auth-subtitle mb-0">Sign in with your PractiLink account.</p></div>
@if(session('success'))<div class="alert alert-success" role="alert"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('login') }}">@csrf
<div class="form-group"><label for="login">Email, username or registration number</label><input type="text" id="login" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" autocomplete="username" required autofocus>@error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="form-group"><label for="password">Password</label><div class="input-group"><input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required><div class="input-group-append"><button type="button" class="btn btn-light border password-toggle" id="togglePassword" aria-label="Show password"><i class="fas fa-eye"></i></button></div></div>@error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
<div class="form-check mb-4"><input type="checkbox" id="remember" name="remember" value="1" class="form-check-input"><label for="remember" class="form-check-label">Remember me</label></div>
<button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold">Sign in</button></form>
<div class="text-center mt-4 pt-3 border-top"><span class="text-muted">New student?</span><a href="{{ route('register') }}" class="font-weight-bold ml-1" style="color:var(--pl-blue-600)">Create a student account</a></div>
</div></div><p class="text-center text-muted small mt-4 mb-0">One secure login for students, staff and administrators.</p>
</div></div></div>
<script>document.getElementById('togglePassword').addEventListener('click',function(){const input=document.getElementById('password');const icon=this.querySelector('i');const visible=input.type==='text';input.type=visible?'password':'text';icon.classList.toggle('fa-eye',visible);icon.classList.toggle('fa-eye-slash',!visible);this.setAttribute('aria-label',visible?'Show password':'Hide password')});</script>
</body></html>
