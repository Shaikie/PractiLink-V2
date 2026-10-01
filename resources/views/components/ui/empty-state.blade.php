<div {{ $attributes->merge(['class' => 'empty-state'.(($compact ?? false) ? ' empty-state-compact' : '')]) }}>
    <div class="empty-state-icon" aria-hidden="true">
        <i class="fas {{ $icon ?? 'fa-inbox' }}"></i>
    </div>
    <h3>{{ $title }}</h3>
    @if (! empty($message))
        <p>{{ $message }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="empty-state-actions">{{ $slot }}</div>
    @endif
</div>
