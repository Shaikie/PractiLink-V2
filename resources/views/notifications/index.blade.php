@extends('layouts.admin')

@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('page_description', 'Updates about your PractiLink activity.')

@section('content')
<div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title">Notification center</h3><form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-sm btn-outline-primary">Mark all as read</button></form></div><div class="card-body p-0">
@if($notifications->isEmpty())<div class="p-4 text-center text-muted"><i class="far fa-bell fa-2x mb-3"></i><p class="mb-0">You're all caught up.</p></div>@else
@foreach($notifications as $notification)
<a href="{{ route('notifications.read', $notification->id) }}" class="d-block p-3 border-bottom text-dark text-decoration-none {{ $notification->read_at ? '' : 'bg-light' }}"><div class="d-flex"><span class="kpi-icon mr-3"><i class="fas fa-bell"></i></span><div><div class="font-weight-bold">{{ $notification->data['title'] ?? 'Notification' }}</div><div class="text-muted small">{{ $notification->data['message'] ?? '' }}</div><div class="text-muted small mt-1">{{ $notification->created_at->diffForHumans() }}</div></div></div></a>
@endforeach
<div class="p-3">{{ $notifications->links() }}</div>@endif
</div></div>
@endsection
