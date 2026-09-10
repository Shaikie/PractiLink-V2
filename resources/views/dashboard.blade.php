@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-lg-4 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $roles->count() }}</h3>
                    <p>Assigned Role{{ $roles->count() === 1 ? '' : 's' }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $user->is_active ? 'Active' : 'Inactive' }}</h3>
                    <p>Account Status</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $user->username }}</h3>
                    <p>Username</p>
                </div>
                <div class="icon">
                    <i class="fas fa-id-badge"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Welcome to PractiLink</h3>
                </div>
                <div class="card-body">
                    <h4>Welcome, {{ $user->fullname }} 👋</h4>
                    <p class="mb-2">
                        You are successfully authenticated and have reached the PractiLink dashboard.
                    </p>
                    <p class="mb-0">
                        <strong>Role:</strong>
                        {{ $roles->isNotEmpty() ? $roles->join(', ') : 'No role assigned' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Account</h3>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $user->fullname }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Username:</strong> {{ $user->username }}</p>
                    <p class="mb-0"><strong>Phone:</strong> {{ $user->phone ?: 'Not provided' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
