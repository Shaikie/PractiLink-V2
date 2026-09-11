@extends('layouts.admin')
@section('title', 'My Applications')
@section('page_title', 'My Applications')
@section('page_description', 'Track your practical training applications and their current progress.')
@section('page_actions')<a href="{{ route('student.applications.create') }}" class="btn btn-primary clay-btn-primary"><i class="fas fa-plus mr-1"></i>Start Application</a>@endsection
@section('content')
@if($errors->any())<div class="alert alert-danger clay-card border-0 mb-3"><strong>Please correct the following:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="clay-card"><div class="clay-table-wrap"><table class="table clay-table mb-0"><thead><tr><th>Reference</th><th>Training</th><th>Study year</th><th>Training dates</th><th>Status</th><th class="text-right">Action</th></tr></thead><tbody>
@forelse($applications as $application)
<tr><td><div class="font-weight-bold">{{ $application->reference_number }}</div><div class="clay-muted small">{{ $application->applicationWindow->name }}</div></td><td>{{ $application->applicationWindow->trainingType->name }}</td><td>{{ $application->current_study_year ? 'Year '.$application->current_study_year : '—' }}</td><td>{{ $application->training_start_date?->format('d M Y') }} – {{ $application->training_end_date?->format('d M Y') }}</td><td><span class="badge clay-badge badge-{{ in_array($application->status,['ACCEPTED','COMPLETED']) ? 'success' : ($application->status === 'REJECTED' ? 'danger' : ($application->status === 'DRAFT' ? 'warning' : 'primary')) }}">{{ str_replace('_',' ',$application->status) }}</span></td><td class="text-right"><a href="{{ route('student.applications.show',$application) }}" class="btn btn-sm btn-outline-primary clay-btn-outline">View</a></td></tr>
@empty
<tr><td colspan="6"><div class="clay-empty-state"><div class="clay-empty-icon"><i class="fas fa-file-alt"></i></div><h3>No applications yet</h3><p>Start a new application when a practical training window is open.</p><a href="{{ route('student.applications.create') }}" class="btn btn-primary clay-btn-primary">Start Application</a></div></td></tr>
@endforelse
</tbody></table></div></div>
@endsection
