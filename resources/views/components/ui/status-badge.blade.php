@php
    $normalizedStatus = strtoupper((string) $status);
    $tone = $tone ?? match ($normalizedStatus) {
        'ACCEPTED', 'ACTIVE', 'APPROVED', 'COMPLETED', 'PUBLISHED', 'SUCCESS' => 'success',
        'ALLOCATED', 'SUBMITTED', 'UNDER_REVIEW' => 'info',
        'DRAFT', 'PENDING', 'RETURNED' => 'warning',
        'CANCELLED', 'FAILED', 'REJECTED' => 'danger',
        default => 'neutral',
    };
    $displayLabel = $label ?? \Illuminate\Support\Str::headline($status);
@endphp

<span {{ $attributes->merge(['class' => 'status-badge status-badge-'.$tone]) }}>
    <span class="status-badge-dot" aria-hidden="true"></span>
    {{ $displayLabel }}
</span>
