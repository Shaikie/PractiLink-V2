@extends('layouts.admin')

@section('title', 'My Profile')
@section('page_title', 'My Profile')
@section('page_description', 'Manage your system account details and password.')

@section('content')
<div class="row">
    <div class="col-lg-7 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Account details</h3></div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    <div class="form-group"><label for="fullname">Full name</label><input id="fullname" type="text" name="fullname" class="form-control" value="{{ old('fullname', $user->fullname) }}" required></div>
                    <div class="form-group"><label for="username">Username</label><input id="username" type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required></div>
                    <div class="form-group"><label for="email">Email</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
                    <div class="form-group mb-0"><label for="phone">Phone</label><input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}"></div>
                </div>
                <div class="card-footer text-right"><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save changes</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-5 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Change password</h3></div>
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group"><label for="current_password">Current password</label><input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group"><label for="password">New password</label><input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="form-group mb-0"><label for="password_confirmation">Confirm new password</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control" minlength="8" required></div>
                </div>
                <div class="card-footer"><button type="submit" class="btn btn-success btn-block"><i class="fas fa-key mr-1"></i> Change password</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
