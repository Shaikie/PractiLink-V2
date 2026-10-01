@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_description', $accountType === 'student' ? 'Track your applications and next training steps.' : 'Monitor reviews, application windows and placement activity.')

@section('page_actions')
    @if($accountType === 'student')
        <a href="{{ route('student.applications.create') }}" class="btn btn-primary">
            <i class="fas fa-plus" aria-hidden="true"></i> Start application
        </a>
    @elseif($permissions->contains('applications.view'))
        <a href="{{ route('admin.applications.index') }}" class="btn btn-primary">
            <i class="fas fa-inbox" aria-hidden="true"></i> Review queue
        </a>
    @endif
@endsection

@section('content')
    @if($accountType === 'staff')
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-3">
                <a href="{{ route('admin.applications.index') }}" class="pl-stat-card card d-block p-3 mb-0">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="pl-stat-label">Review queue</div>
                            <div class="pl-stat-value mt-2">{{ $reviewQueueCount }}</div>
                            <div class="pl-stat-detail">Submitted or returned applications</div>
                        </div>
                        <span class="pl-stat-icon"><i class="fas fa-inbox" aria-hidden="true"></i></span>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="pl-stat-card card p-3 mb-0">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="pl-stat-label">Open windows</div>
                            <div class="pl-stat-value mt-2">{{ $openWindowCount }}</div>
                            <div class="pl-stat-detail">Accepting applications now</div>
                        </div>
                        <span class="pl-stat-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="pl-stat-card card p-3 mb-0">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="pl-stat-label">Active placements</div>
                            <div class="pl-stat-value mt-2">{{ $activePlacementCount }}</div>
                            <div class="pl-stat-detail">Allocated or in progress</div>
                        </div>
                        <span class="pl-stat-icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="pl-stat-card card is-success p-3 mb-0">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="pl-stat-label">Signed in as</div>
                            <div class="pl-stat-value mt-2">{{ $roles->first() ?? 'User' }}</div>
                            <div class="pl-stat-detail">{{ $account->username }}</div>
                        </div>
                        <span class="pl-stat-icon"><i class="fas fa-shield-alt" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">Recent application activity</h3>
                        @if($permissions->contains('applications.view'))
                            <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-light ml-auto">View all</a>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @forelse($recentApplications as $application)
                            <a href="{{ route('admin.applications.show', $application) }}" class="pl-activity-row">
                                <span class="pl-activity-icon"><i class="fas fa-file-alt" aria-hidden="true"></i></span>
                                <span class="pl-activity-copy">
                                    <strong>{{ $application->reference_number }}</strong>
                                    <small>{{ $application->student->full_name }} · {{ $application->applicationWindow->trainingType->name }}</small>
                                </span>
                                <x-ui.status-badge :status="$application->status" />
                                <time datetime="{{ $application->submitted_at?->toIso8601String() }}">{{ $application->submitted_at?->format('d M') ?? 'Not submitted' }}</time>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @empty
                            <x-ui.empty-state
                                icon="fa-inbox"
                                title="No review activity yet"
                                message="Applications assigned to your team will appear here."
                                compact
                            />
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Quick actions</h3></div>
                    <div class="card-body d-flex flex-column gap-2">
                        @if($permissions->contains('applications.view'))
                            <a href="{{ route('admin.applications.index') }}" class="pl-quick-link">
                                <span class="pl-quick-link-icon"><i class="fas fa-inbox" aria-hidden="true"></i></span>
                                <span class="pl-quick-link-copy"><strong>Review applications</strong><small>Open the staff review queue</small></span>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if($permissions->contains('applications.manage'))
                            <a href="{{ route('admin.application-windows.index') }}" class="pl-quick-link">
                                <span class="pl-quick-link-icon"><i class="fas fa-calendar-plus" aria-hidden="true"></i></span>
                                <span class="pl-quick-link-copy"><strong>Manage windows</strong><small>Create and update application periods</small></span>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if($permissions->contains('workflows.manage'))
                            <a href="{{ route('admin.workflows.index') }}" class="pl-quick-link">
                                <span class="pl-quick-link-icon"><i class="fas fa-project-diagram" aria-hidden="true"></i></span>
                                <span class="pl-quick-link-copy"><strong>Manage workflows</strong><small>Configure review stages and transitions</small></span>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if($permissions->contains('users.manage'))
                            <a href="{{ route('admin.reference-data.index') }}" class="pl-quick-link">
                                <span class="pl-quick-link-icon"><i class="fas fa-sliders-h" aria-hidden="true"></i></span>
                                <span class="pl-quick-link-copy"><strong>Configure reference data</strong><small>Manage departments, academic data and document types</small></span>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if($permissions->contains('organizations.manage'))
                            <a href="{{ route('admin.organizations.index') }}" class="pl-quick-link">
                                <span class="pl-quick-link-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                                <span class="pl-quick-link-copy"><strong>Manage organizations</strong><small>Keep placement organizations current</small></span>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if($permissions->contains('students.manage'))
                            <a href="{{ route('admin.students.index') }}" class="pl-quick-link">
                                <span class="pl-quick-link-icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></span>
                                <span class="pl-quick-link-copy"><strong>Student directory</strong><small>Review profiles and account access</small></span>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if($permissions->contains('users.manage'))
                            <a href="{{ route('admin.staff.index') }}" class="pl-quick-link">
                                <span class="pl-quick-link-icon"><i class="fas fa-users-cog" aria-hidden="true"></i></span>
                                <span class="pl-quick-link-copy"><strong>Staff access</strong><small>Manage roles and account status</small></span>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-3">
                <a href="{{ route('student.applications.index') }}" class="pl-stat-card card d-block p-3 mb-0">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="pl-stat-label">Applications</div>
                            <div class="pl-stat-value mt-2">{{ $applicationCount }}</div>
                            <div class="pl-stat-detail">Across all training windows</div>
                        </div>
                        <span class="pl-stat-icon"><i class="fas fa-file-alt" aria-hidden="true"></i></span>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="pl-stat-card card p-3 mb-0">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="pl-stat-label">In progress</div>
                            <div class="pl-stat-value mt-2">{{ $activeApplicationCount }}</div>
                            <div class="pl-stat-detail">Submitted, returned or accepted</div>
                        </div>
                        <span class="pl-stat-icon"><i class="fas fa-spinner" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="pl-stat-card card p-3 mb-0">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="pl-stat-label">Documents</div>
                            <div class="pl-stat-value mt-2">{{ $documentCount }}</div>
                            <div class="pl-stat-detail">Uploaded supporting files</div>
                        </div>
                        <span class="pl-stat-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="pl-stat-card card is-success p-3 mb-0">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="pl-stat-label">Placement</div>
                            <div class="pl-stat-value mt-2">{{ $placement?->status ? \Illuminate\Support\Str::headline($placement->status) : 'Not assigned' }}</div>
                            <div class="pl-stat-detail">{{ $placement?->organization->name ?? 'Complete your application to begin' }}</div>
                        </div>
                        <span class="pl-stat-icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">Your applications</h3>
                        <a href="{{ route('student.applications.index') }}" class="btn btn-sm btn-light ml-auto">View all</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse($recentApplications as $application)
                            <a href="{{ route('student.applications.show', $application) }}" class="pl-activity-row">
                                <span class="pl-activity-icon"><i class="fas fa-file-alt" aria-hidden="true"></i></span>
                                <span class="pl-activity-copy">
                                    <strong>{{ $application->applicationWindow->name }}</strong>
                                    <small>{{ $application->reference_number }} · {{ $application->applicationWindow->trainingType->name }}</small>
                                </span>
                                <x-ui.status-badge :status="$application->status" />
                                <time datetime="{{ $application->updated_at?->toIso8601String() }}">{{ $application->updated_at?->diffForHumans() }}</time>
                                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        @empty
                            <x-ui.empty-state
                                icon="fa-file-plus"
                                title="Start your first application"
                                message="Choose an open training window to begin your practical training application."
                                compact
                            >
                                <a href="{{ route('student.applications.create') }}" class="btn btn-primary">Start application</a>
                            </x-ui.empty-state>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Next steps</h3></div>
                    <div class="card-body">
                        <div class="pl-next-step">
                            <span>1</span>
                            <div><strong>Complete your profile</strong><p>Keep your academic and contact information current.</p></div>
                        </div>
                        <div class="pl-next-step">
                            <span>2</span>
                            <div><strong>Prepare an application</strong><p>Choose an open window and provide the required details.</p></div>
                        </div>
                        <div class="pl-next-step">
                            <span>3</span>
                            <div><strong>Upload documents</strong><p>Attach every required file before submitting.</p></div>
                        </div>
                        <div class="pl-next-step">
                            <span>4</span>
                            <div><strong>Follow review progress</strong><p>Track status updates and placement details here.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
