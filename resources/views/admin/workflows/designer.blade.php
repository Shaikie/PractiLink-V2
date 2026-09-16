@extends('layouts.admin')

@section('title', $workflow->name)

@section('page_title', $workflow->name)

@section(
    'page_description',
    'Design the application journey without touching code.'
)

@section('page_actions')
    <form
        method="POST"
        action="{{ route('admin.workflows.versions.store', $workflow) }}"
    >
        @csrf

        <input
            type="hidden"
            name="change_summary"
            value="New workflow revision"
        >

        <button class="btn btn-primary clay-btn-primary">
            <i class="fas fa-code-branch mr-1"></i>
            New version
        </button>
    </form>
@endsection

@section('content')

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Workflow needs attention</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="clay-card mb-4">

    </div>

    <div class="clay-card mb-4">
        <div class="clay-card-body">

            <h3 class="h5 font-weight-bold">
                Versions
            </h3>

            <p class="clay-muted small">
                Published versions are locked so existing applications keep
                their original journey.
            </p>

            <div class="table-responsive">
                <table class="table clay-table mb-0">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Summary</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($workflow->versions->sortByDesc('version') as $version)
                            <tr>
                                <td>
                                    v{{ $version->version }}
                                </td>

                                <td>
                                    <span
                                        class="badge badge-{{
                                            $version->status === 'PUBLISHED'
                                                ? 'success'
                                                : (
                                                    $version->status === 'DRAFT'
                                                        ? 'warning'
                                                        : 'secondary'
                                                )
                                        }}"
                                    >
                                        {{ $version->status }}
                                    </span>
                                </td>

                                <td>
                                    {{
                                        $version->published_at?->format('d M Y H:i')
                                        ?? '—'
                                    }}
                                </td>

                                <td>
                                    {{ $version->change_summary ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    @if($draft)

        <form
            method="POST"
            action="{{ route('admin.workflows.versions.update', $draft) }}"
            id="workflow-editor"
        >
            @csrf
            @method('PUT')

            <input
                type="hidden"
                name="starting_stage_code"
                id="starting-stage"
                value="{{ $draft->stages->firstWhere('is_starting', true)?->code }}"
            >

            {{-- =========================
                 STAGES
            ========================== --}}
            <div class="clay-card mb-4">
                <div class="clay-card-body">

                    <div class="d-flex align-items-center mb-3">
                        <span class="clay-step mr-2">
                            1
                        </span>

                        <div>
                            <h3 class="h5 font-weight-bold mb-1">
                                Stages
                            </h3>

                            <p class="clay-muted small mb-0">
                                Name the steps and assign the team responsible
                                for each one.
                            </p>
                        </div>
                    </div>

                    <div id="stage-list">

                        @foreach($draft->stages->sortBy('stage_order') as $i => $stage)

                            <div class="border rounded p-3 mb-3 stage-row">

                                <div
                                    class="d-flex justify-content-between align-items-center mb-3"
                                >
                                    <div>
                                        <span class="badge badge-light stage-number">
                                            Stage {{ $i + 1 }}
                                        </span>

                                        <span class="font-weight-bold stage-title">
                                            {{ $stage->name }}
                                        </span>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger remove-stage"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <div class="form-row">

                                    <div class="form-group col-md-6">
                                        <label>
                                            Stage name
                                        </label>

                                        <input
                                            name="stages[{{ $i }}][name]"
                                            class="form-control stage-name"
                                            value="{{ $stage->name }}"
                                            required
                                        >
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>
                                            Responsible role
                                        </label>

                                        <select
                                            name="stages[{{ $i }}][responsible_role_id]"
                                            class="custom-select"
                                        >
                                            <option value="">
                                                Any authorized reviewer
                                            </option>

                                            @foreach($roles as $role)
                                                <option
                                                    value="{{ $role->id }}"
                                                    @selected(
                                                        $stage->responsible_role_id == $role->id
                                                    )
                                                >
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="stages[{{ $i }}][code]"
                                        class="stage-code"
                                        value="{{ $stage->code }}"
                                    >

                                    <div class="form-group col-md-6 mb-0">
                                        <label>
                                            Stage purpose
                                        </label>

                                        <select
                                            class="custom-select start-stage-select"
                                        >
                                            <option value="0">
                                                Normal stage
                                            </option>

                                            <option
                                                value="1"
                                                @selected($stage->is_starting)
                                            >
                                                Starting stage
                                            </option>
                                        </select>
                                    </div>

                                    <div
                                        class="form-group col-md-6 mb-0 d-flex align-items-end"
                                    >
                                        <label class="mb-0">
                                            <input
                                                type="checkbox"
                                                name="stages[{{ $i }}][is_terminal]"
                                                value="1"
                                                @checked($stage->is_terminal)
                                            >

                                            Final stage — workflow ends here
                                        </label>
                                    </div>

                                </div>
                            </div>

                        @endforeach

                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        id="add-stage"
                    >
                        <i class="fas fa-plus mr-1"></i>
                        Add stage
                    </button>

                </div>
            </div>

            {{-- =========================
                 TRANSITIONS
            ========================== --}}
            <div class="clay-card mb-4">
                <div class="clay-card-body">

                    <div class="d-flex align-items-center mb-3">
                        <span class="clay-step mr-2">
                            2
                        </span>

                        <div>
                            <h3 class="h5 font-weight-bold mb-1">
                                Transitions
                            </h3>

                            <p class="clay-muted small mb-0">
                                Define what a reviewer can do from each stage.
                            </p>
                        </div>
                    </div>

                    <div id="transition-list">

                        @foreach($draft->transitions as $i => $transition)

                            <div class="border rounded p-3 mb-3 transition-row">

                                <div class="form-row">

                                    <div class="form-group col-md-4">
                                        <label>
                                            From
                                        </label>

                                        <select
                                            name="transitions[{{ $i }}][from_code]"
                                            class="custom-select from-code"
                                            required
                                        ></select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>
                                            Action
                                        </label>

                                        <select
                                            name="transitions[{{ $i }}][action]"
                                            class="custom-select action-select"
                                            required
                                        >
                                            @foreach($actions as $key => $action)
                                                <option
                                                    value="{{ $key }}"
                                                    @selected($transition->action === $key)
                                                >
                                                    {{ $action['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>
                                            To
                                        </label>

                                        <select
                                            name="transitions[{{ $i }}][to_code]"
                                            class="custom-select to-code"
                                            required
                                        ></select>
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label>
                                            Button label
                                        </label>

                                        <input
                                            name="transitions[{{ $i }}][label]"
                                            class="form-control label-input"
                                            value="{{ $transition->label }}"
                                            required
                                        >
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>
                                            Application outcome
                                        </label>

                                        <select
                                            name="transitions[{{ $i }}][result_status]"
                                            class="custom-select result-status"
                                            required
                                        >
                                            @foreach($resultStatuses as $key => $label)
                                                <option
                                                    value="{{ $key }}"
                                                    @selected(
                                                        $transition->result_status === $key
                                                    )
                                                >
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label>
                                            Reviewer role
                                        </label>

                                        <select
                                            name="transitions[{{ $i }}][responsible_role_id]"
                                            class="custom-select"
                                        >
                                            <option value="">
                                                Stage role
                                            </option>

                                            @foreach($roles as $role)
                                                <option
                                                    value="{{ $role->id }}"
                                                    @selected(
                                                        $transition->responsible_role_id == $role->id
                                                    )
                                                >
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-12 mb-0">

                                        <label>
                                            <input
                                                type="checkbox"
                                                name="transitions[{{ $i }}][requires_comment]"
                                                value="1"
                                                @checked($transition->requires_comment)
                                            >

                                            Require a comment
                                        </label>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger float-right remove-transition"
                                        >
                                            <i class="fas fa-trash mr-1"></i>
                                            Remove
                                        </button>

                                    </div>

                                </div>
                            </div>

                        @endforeach

                    </div>

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        id="add-transition"
                    >
                        <i class="fas fa-plus mr-1"></i>
                        Add transition
                    </button>

                </div>
            </div>

            {{-- =========================
                 FORM ACTIONS
            ========================== --}}
            <div class="clay-card">
                <div
                    class="clay-card-footer d-flex justify-content-between"
                >

                    <a
                        href="{{ route('admin.workflows.index') }}"
                        class="btn btn-light"
                    >
                        Back
                    </a>

                    <div>

                        <button class="btn btn-primary mr-2">
                            <i class="fas fa-save mr-1"></i>
                            Save draft
                        </button>

                        <button
                            formaction="{{ route('admin.workflows.versions.publish', $draft) }}"
                            formmethod="POST"
                            class="btn btn-success"
                            onclick="return confirm('Publish this workflow? Published versions cannot be edited.')"
                        >
                            <i class="fas fa-check mr-1"></i>
                            Publish
                        </button>

                    </div>

                </div>
            </div>

        </form>

    @else

        <div class="clay-card">
            <div class="clay-card-body text-center py-5">

                <h3 class="h5">
                    No editable draft
                </h3>

                <p class="clay-muted">
                    Create a new version to safely change the workflow.
                </p>

            </div>
        </div>

    @endif

@endsection

@push('scripts')
    <script>
        (function () {
            const stagesEl = document.getElementById('stage-list');
            const transitionsEl = document.getElementById('transition-list');
            const start = document.getElementById('starting-stage');

            if (!stagesEl) {
                return;
            }

            const roles = @json(
                $roles->map(
                    fn ($r) => [
                        'id' => $r->id,
                        'name' => $r->name,
                    ]
                )->values()
            );

            const actions = @json($actions);
            const statuses = @json($resultStatuses);

            const esc = value =>
                String(value ?? '').replace(
                    /[&<>"']/g,
                    match => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;',
                    }[match])
                );

            const slug = value =>
                String(value || '')
                    .trim()
                    .toUpperCase()
                    .replace(/[^A-Z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '')
                    .slice(0, 100);

            const roleOpts = (selected = '') =>
                '<option value="">Stage role</option>' +
                roles
                    .map(
                        role =>
                            `<option value="${role.id}" ${
                                String(selected) === String(role.id)
                                    ? 'selected'
                                    : ''
                            }>${esc(role.name)}</option>`
                    )
                    .join('');

            const data = () =>
                [...stagesEl.querySelectorAll('.stage-row')]
                    .map(row => ({
                        row,
                        code: row.querySelector('.stage-code').value,
                        name: row.querySelector('.stage-name').value,
                    }))
                    .filter(stage => stage.code);

            const opts = selected =>
                data()
                    .map(
                        stage =>
                            `<option value="${esc(stage.code)}" ${
                                selected === stage.code
                                    ? 'selected'
                                    : ''
                            }>${esc(stage.name)}</option>`
                    )
                    .join('');

            const actionOpts = selected =>
                Object.entries(actions)
                    .map(
                        ([key, action]) =>
                            `<option value="${key}" ${
                                selected === key
                                    ? 'selected'
                                    : ''
                            }>${esc(action.label)}</option>`
                    )
                    .join('');

            const statusOpts = selected =>
                Object.entries(statuses)
                    .map(
                        ([key, value]) =>
                            `<option value="${key}" ${
                                selected === key
                                    ? 'selected'
                                    : ''
                            }>${esc(value)}</option>`
                    )
                    .join('');

            function refresh() {
                transitionsEl
                    .querySelectorAll('.transition-row')
                    .forEach(row => {
                        const from = row.querySelector('.from-code');
                        const to = row.querySelector('.to-code');

                        const fromValue = from.value;
                        const toValue = to.value;

                        from.innerHTML = opts(fromValue);
                        to.innerHTML = opts(toValue);

                        from.value = fromValue;
                        to.value = toValue;
                    });

                stagesEl
                    .querySelectorAll('.start-stage-select')
                    .forEach(select => {
                        select.value =
                            select
                                .closest('.stage-row')
                                .querySelector('.stage-code')
                                .value === start.value
                                ? '1'
                                : '0';
                    });
            }

            function reindex() {
                stagesEl
                    .querySelectorAll('.stage-row')
                    .forEach((row, index) => {
                        row.querySelector('.stage-number').textContent =
                            'Stage ' + (index + 1);

                        row.querySelectorAll('[name]').forEach(element => {
                            element.name = element.name.replace(
                                /stages\[\d+\]/,
                                `stages[${index}]`
                            );
                        });
                    });

                transitionsEl
                    .querySelectorAll('.transition-row')
                    .forEach((row, index) => {
                        row.querySelectorAll('[name]').forEach(element => {
                            element.name = element.name.replace(
                                /transitions\[\d+\]/,
                                `transitions[${index}]`
                            );
                        });
                    });

                refresh();
            }

            function addStage() {
                const index =
                    stagesEl.querySelectorAll('.stage-row').length;

                const row = document.createElement('div');

                row.className =
                    'border rounded p-3 mb-3 stage-row';

                row.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge badge-light stage-number">
                                Stage ${index + 1}
                            </span>

                            <span class="font-weight-bold stage-title">
                                New stage
                            </span>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger remove-stage"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-6">
                            <label>
                                Stage name
                            </label>

                            <input
                                name="stages[${index}][name]"
                                class="form-control stage-name"
                                required
                            >
                        </div>

                        <div class="form-group col-md-6">
                            <label>
                                Responsible role
                            </label>

                            <select
                                name="stages[${index}][responsible_role_id]"
                                class="custom-select"
                            >
                                ${roleOpts()}
                            </select>
                        </div>

                        <input
                            type="hidden"
                            name="stages[${index}][code]"
                            class="stage-code"
                        >

                        <div class="form-group col-md-6 mb-0">
                            <label>
                                Stage purpose
                            </label>

                            <select class="custom-select start-stage-select">
                                <option value="0">
                                    Normal stage
                                </option>

                                <option value="1">
                                    Starting stage
                                </option>
                            </select>
                        </div>

                        <div class="form-group col-md-6 mb-0 d-flex align-items-end">
                            <label class="mb-0">
                                <input
                                    type="checkbox"
                                    name="stages[${index}][is_terminal]"
                                    value="1"
                                >

                                Final stage — workflow ends here
                            </label>
                        </div>

                    </div>
                `;

                stagesEl.appendChild(row);

                reindex();
            }

            function addTransition() {
                const index =
                    transitionsEl.querySelectorAll(
                        '.transition-row'
                    ).length;

                const stages = data();

                const row = document.createElement('div');

                row.className =
                    'border rounded p-3 mb-3 transition-row';

                row.innerHTML = `
                    <div class="form-row">

                        <div class="form-group col-md-4">
                            <label>
                                From
                            </label>

                            <select
                                name="transitions[${index}][from_code]"
                                class="custom-select from-code"
                                required
                            >
                                ${opts(stages[0]?.code)}
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>
                                Action
                            </label>

                            <select
                                name="transitions[${index}][action]"
                                class="custom-select action-select"
                                required
                            >
                                ${actionOpts('FORWARD')}
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>
                                To
                            </label>

                            <select
                                name="transitions[${index}][to_code]"
                                class="custom-select to-code"
                                required
                            >
                                ${opts(
                                    stages[1]?.code ||
                                    stages[0]?.code
                                )}
                            </select>
                        </div>

                        <div class="form-group col-md-5">
                            <label>
                                Button label
                            </label>

                            <input
                                name="transitions[${index}][label]"
                                class="form-control label-input"
                                value="Forward"
                                required
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label>
                                Application outcome
                            </label>

                            <select
                                name="transitions[${index}][result_status]"
                                class="custom-select result-status"
                                required
                            >
                                ${statusOpts('UNDER_REVIEW')}
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>
                                Reviewer role
                            </label>

                            <select
                                name="transitions[${index}][responsible_role_id]"
                                class="custom-select"
                            >
                                ${roleOpts()}
                            </select>
                        </div>

                        <div class="form-group col-md-12 mb-0">

                            <label>
                                <input
                                    type="checkbox"
                                    name="transitions[${index}][requires_comment]"
                                    value="1"
                                >

                                Require a comment
                            </label>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger float-right remove-transition"
                            >
                                Remove
                            </button>

                        </div>

                    </div>
                `;

                transitionsEl.appendChild(row);

                reindex();
            }

            stagesEl.addEventListener('input', event => {
                if (!event.target.classList.contains('stage-name')) {
                    return;
                }

                const row = event.target.closest('.stage-row');

                row.querySelector('.stage-code').value =
                    slug(event.target.value);

                row.querySelector('.stage-title').textContent =
                    event.target.value || 'New stage';

                refresh();
            });

            stagesEl.addEventListener('change', event => {
                if (
                    event.target.classList.contains(
                        'start-stage-select'
                    ) &&
                    event.target.value === '1'
                ) {
                    start.value =
                        event.target
                            .closest('.stage-row')
                            .querySelector('.stage-code')
                            .value;

                    refresh();
                }
            });

            stagesEl.addEventListener('click', event => {
                if (!event.target.closest('.remove-stage')) {
                    return;
                }

                if (data().length <= 1) {
                    return alert(
                        'A workflow needs at least one stage.'
                    );
                }

                event.target
                    .closest('.remove-stage')
                    .closest('.stage-row')
                    .remove();

                if (
                    !data().some(
                        stage => stage.code === start.value
                    )
                ) {
                    start.value = data()[0]?.code || '';
                }

                reindex();
            });

            transitionsEl.addEventListener('change', event => {
                if (
                    !event.target.classList.contains(
                        'action-select'
                    )
                ) {
                    return;
                }

                const row =
                    event.target.closest('.transition-row');

                const action =
                    actions[event.target.value];

                if (action) {
                    row.querySelector('.label-input').value =
                        action.label;

                    row.querySelector('.result-status').value =
                        action.result_status;
                }
            });

            transitionsEl.addEventListener('click', event => {
                if (
                    !event.target.closest(
                        '.remove-transition'
                    )
                ) {
                    return;
                }

                event.target
                    .closest('.remove-transition')
                    .closest('.transition-row')
                    .remove();

                reindex();
            });

            document
                .getElementById('add-stage')
                ?.addEventListener('click', addStage);

            document
                .getElementById('add-transition')
                ?.addEventListener(
                    'click',
                    addTransition
                );

            refresh();
        })();
    </script>
@endpush