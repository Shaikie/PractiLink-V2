@extends('layouts.admin')

@section('title', 'My Applications')
@section('page_title', 'My Applications')
@section('page_description', 'Track every practical training application and its current progress.')

@section('page_actions')
    <a href="{{ route('student.applications.create') }}" class="btn btn-primary">
        <i class="fas fa-plus" aria-hidden="true"></i> Start application
    </a>
@endsection

@section('content')
    <x-ui.validation-summary class="mb-3" />

    @if($applications->contains(fn ($application) => $application->status === 'RETURNED'))
        <div class="alert alert-warning d-flex align-items-start gap-3" role="alert">
            <i class="fas fa-undo-alt mt-1" aria-hidden="true"></i>
            <div>
                <strong>Action required</strong>
                <div class="mt-1">One or more applications were returned. Review the reviewer comments and update your submission.</div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Application history</h3>
                <p class="mb-0 mt-1 text-muted small">{{ $applications->total() }} {{ \Illuminate\Support\Str::plural('application', $applications->total()) }} on record</p>
            </div>
        </div>

        @if($applications->isEmpty())
            <div class="card-body">
                <x-ui.empty-state
                    icon="fa-file-plus"
                    title="No applications yet"
                    message="Start an application when a practical training window is open. You can save a draft before submitting."
                >
                    <a href="{{ route('student.applications.create') }}" class="btn btn-primary">Start application</a>
                </x-ui.empty-state>
            </div>
        @else
            <div class="table-responsive">
                <table class="table clay-table mb-0">
                    <thead>
                        <tr>
                            <th>Application</th>
                            <th>Training</th>
                            <th>Study year</th>
                            <th>Training dates</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $application)
                            <tr>
                                <td>
                                    <strong class="d-block">{{ $application->reference_number }}</strong>
                                    <span class="small text-muted">{{ $application->applicationWindow->name }}</span>
                                </td>
                                <td>{{ $application->applicationWindow->trainingType->name }}</td>
                                <td>{{ $application->current_study_year ? 'Year '.$application->current_study_year : '—' }}</td>
                                <td>
                                    @if($application->training_start_date && $application->training_end_date)
                                        <span class="d-block">{{ $application->training_start_date->format('d M Y') }}</span>
                                        <span class="small text-muted">to {{ $application->training_end_date->format('d M Y') }}</span>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td><x-ui.status-badge :status="$application->status" /></td>
                                <td class="text-right">
                                    <a href="{{ route('student.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">
                                        View details <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
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
