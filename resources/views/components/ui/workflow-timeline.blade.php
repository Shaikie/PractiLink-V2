@php
    $timelineEvents = $events->sortBy('acted_at')->values();
    $lastIndex = $timelineEvents->count() - 1;
@endphp

<ol {{ $attributes->merge(['class' => 'workflow-timeline']) }}>
    @forelse ($timelineEvents as $index => $event)
        <li class="workflow-timeline-item {{ $index === $lastIndex ? 'is-current' : 'is-complete' }}">
            <span class="workflow-timeline-marker" aria-hidden="true">
                @if ($index === $lastIndex)
                    <span class="workflow-timeline-pulse"></span>
                @else
                    <i class="fas fa-check"></i>
                @endif
            </span>
            <div class="workflow-timeline-content">
                <div class="workflow-timeline-heading">
                    <h3>{{ $event->toStage?->name ?? 'Workflow started' }}</h3>
                    <time datetime="{{ $event->acted_at?->toIso8601String() }}">
                        {{ $event->acted_at?->format('d M Y, H:i') ?? 'Date unavailable' }}
                    </time>
                </div>
                @if ($event->actor)
                    <p class="workflow-timeline-actor">By {{ $event->actor->fullname }}</p>
                @endif
                @if ($event->comment)
                    <p>{{ $event->comment }}</p>
                @else
                    <p class="text-muted">No additional comment was provided.</p>
                @endif
            </div>
        </li>
    @empty
        <li class="workflow-timeline-empty text-muted">No workflow activity has been recorded yet.</li>
    @endforelse
</ol>
