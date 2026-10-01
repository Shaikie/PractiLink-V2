@php
    $isActive = request()->routeIs($pattern);
@endphp

<li class="nav-item">
    <a href="{{ $route }}" @class(['nav-link', 'active' => $isActive]) @if($isActive) aria-current="page" @endif>
        <i class="nav-icon fas {{ $icon }}" aria-hidden="true"></i>
        <p>
            {{ $label }}
            @if (! empty($badge ?? null))
                <span class="right badge badge-success">{{ $badge > 99 ? '99+' : $badge }}</span>
            @endif
        </p>
    </a>
</li>
