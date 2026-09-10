@extends('layouts.admin')

@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Account Details</h3>
                </div>
                <form method="POST" action="{{ route('profile.update') }}">
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

                        <div class="form-group">
                            <label for="fullname">Full name</label>
                            <input id="fullname" type="text" name="fullname" class="form-control" value="{{ old('fullname', $user->fullname) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="form-group mb-0">
                            <label for="phone">Phone</label>
                            <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Save Profile Details</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Change Password</h3>
                </div>
                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="current_password">Current password</label>
                            <input id="current_password" type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="password">New password</label>
                            <input id="password" type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group mb-0">
                            <label for="password_confirmation">Confirm new password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">Change Password</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection
