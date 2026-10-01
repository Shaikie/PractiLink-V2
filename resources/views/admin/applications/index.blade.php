@extends('layouts.admin')

@section('title', 'Application Review')
@section('page_title', 'Application review')
@section('page_description', 'Search, triage and progress student applications assigned to your team.')

@section('content')
    <x-ui.validation-summary class="mb-3" />

    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Find an application</h3>
                <p class="mb-0 mt-1 text-muted small">Filter by reference, student, status or current workflow stage.</p>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.applications.index') }}" class="pl-filter-form">
                <div class="form-group mb-2">
                    <label for="search">Search</label>
                    <div class="pl-input-wrap">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Reference, name, email or registration number">
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All statuses</option>
                        @foreach(['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label for="stage">Workflow stage</label>
                    <select id="stage" name="stage" class="form-control">
                        <option value="">All stages</option>
                        @foreach($workflowStages as $stage)
                            <option value="{{ $stage->code }}" @selected(($filters['stage'] ?? '') === $stage->code)>{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pl-filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter" aria-hidden="true"></i> Apply
                    </button>
                    @if(array_filter($filters, fn ($value) => $value !== null && $value !== ''))
                        <a href="{{ route('admin.applications.index') }}" class="btn btn-light">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Application queue</h3>
                @if($applications->total() > 0)
                    <p class="mb-0 mt-1 text-muted small">Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }}</p>
                @endif
            </div>
        </div>

        @if($applications->isEmpty())
            <div class="card-body">
                <x-ui.empty-state
                    icon="fa-inbox"
                    title="No applications found"
                    message="Try changing or clearing the filters. Only applications available to your assigned roles are shown."
                >
                    @if(array_filter($filters, fn ($value) => $value !== null && $value !== ''))
                        <a href="{{ route('admin.applications.index') }}" class="btn btn-primary">Clear filters</a>
                    @endif
                </x-ui.empty-state>
            </div>
        @else
            <div class="table-responsive">
                <table class="table clay-table mb-0">
                    <thead>
                        <tr>
                            <th>Application</th>
                            <th>Student</th>
                            <th>Training</th>
                            <th>Current stage</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $application)
                            <tr>
                                <td>
                                    <strong class="d-block">{{ $application->reference_number }}</strong>
                                    <span class="small text-muted">{{ $application->department?->name ?? 'Department pending' }}</span>
                                </td>
                                <td>
                                    <strong class="d-block">{{ $application->student->full_name }}</strong>
                                    <span class="small text-muted">{{ $application->student->registration_number }}</span>
                                </td>
                                <td>
                                    <span class="d-block">{{ $application->applicationWindow->trainingType->name }}</span>
                                    <span class="small text-muted">{{ $application->applicationWindow->name }}</span>
                                </td>
                                <td>{{ $application->workflow?->currentStage?->name ?? 'Not started' }}</td>
                                <td><x-ui.status-badge :status="$application->status" /></td>
                                <td>
                                    <span class="d-block">{{ $application->submitted_at?->format('d M Y') ?? '—' }}</span>
                                    <span class="small text-muted">{{ $application->submitted_at?->format('H:i') }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">
                                        Review <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="px-3 py-3 border-top">{{ $applications->links() }}</div>
            @endif
        @endif
    </div>
@endsection
