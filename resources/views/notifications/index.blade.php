@extends('layouts.admin')

@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('page_description', 'Updates about your PractiLink activity.')

@section('content')
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Notification center</h3>
                <p class="mb-0 mt-1 text-muted small">{{ $unreadCount }} unread {{ \Illuminate\Support\Str::plural('notification', $unreadCount) }}</p>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}" class="ml-auto">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary" @disabled($unreadCount === 0)>Mark all as read</button>
            </form>
        </div>

        @if($notifications->isEmpty())
            <div class="card-body">
                <x-ui.empty-state icon="far fa-bell" title="You're all caught up" message="New activity and status updates will appear here." compact />
            </div>
        @else
            <div class="notification-list">
                @foreach($notifications as $notification)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="notification-row {{ $notification->read_at ? '' : 'is-unread' }}">
                            <span class="notification-icon"><i class="far fa-bell" aria-hidden="true"></i></span>
                            <span class="notification-copy">
                                <strong>{{ $notification->data['title'] ?? 'Notification' }}</strong>
                                <span>{{ $notification->data['message'] ?? '' }}</span>
                                <time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->diffForHumans() }}</time>
                            </span>
                            @if(! $notification->read_at)
                                <span class="notification-unread-label">Unread</span>
                            @endif
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </button>
                    </form>
                @endforeach
            </div>
            @if($notifications->hasPages())
                <div class="px-3 py-3 border-top">{{ $notifications->links() }}</div>
            @endif
        @endif
    </div>
@endsection
