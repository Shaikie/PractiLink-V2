@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_description', $accountType === 'student' ? 'Your PractiLink student workspace.' : 'Your PractiLink administration workspace.')

@section('content')
<div class="row">
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="kpi-card card mb-0 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="kpi-label">Account</div><div class="kpi-value mt-2">Active</div><div class="text-muted small">{{ $accountType === 'student' ? 'Student account' : 'System account' }}</div></div>
                <div class="kpi-icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-3">
        <div class="kpi-card success card mb-0 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="kpi-label">Identity</div><div class="kpi-value mt-2">{{ $accountType === 'student' ? $account->registration_number : $account->username }}</div><div class="text-muted small">Unique account identifier</div></div>
                <div class="kpi-icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12 mb-3">
        <div class="kpi-card card mb-0 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="kpi-label">Access</div><div class="kpi-value mt-2">{{ $accountType === 'student' ? 'Student' : ($roles->isNotEmpty() ? $roles->first() : 'User') }}</div><div class="text-muted small">Current account type</div></div>
                <div class="kpi-icon"><i class="fas fa-shield-alt"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Welcome to PractiLink</h3></div>
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="kpi-icon mr-3"><i class="fas fa-link"></i></div>
                    <div>
                        <h4 class="mt-0 mb-2">Welcome, {{ $accountType === 'student' ? $account->full_name : $account->fullname }} 👋</h4>
                        <p class="text-muted mb-0">You are signed in through PractiLink's unified authentication system. Your {{ $accountType }} account remains independent from the other account domain.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Account details</h3></div>
            <div class="card-body">
                @if ($accountType === 'student')
                    <p><strong>Name:</strong> {{ $account->full_name }}</p>
                    <p><strong>Email:</strong> {{ $account->email }}</p>
                    <p><strong>Institution:</strong> {{ $account->institution->name }}</p>
                    <p class="mb-0"><strong>Study level:</strong> {{ $account->studyLevel->name }}</p>
                @else
                    <p><strong>Name:</strong> {{ $account->fullname }}</p>
                    <p><strong>Email:</strong> {{ $account->email }}</p>
                    <p><strong>Username:</strong> {{ $account->username }}</p>
                    <p class="mb-0"><strong>Phone:</strong> {{ $account->phone ?: 'Not provided' }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
