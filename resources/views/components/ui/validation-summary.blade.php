@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'alert alert-danger validation-summary', 'role' => 'alert']) }}>
        <div class="validation-summary-icon" aria-hidden="true">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <strong>{{ $title ?? 'Please review the highlighted information.' }}</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
