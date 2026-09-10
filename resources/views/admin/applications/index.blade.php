@extends('layouts.admin')

@section('title', 'Application Review')
@section('page_title', 'Application Review')
@section('page_description', 'Review student practical training applications.')

@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Applications</h3></div><div class="card-body p-0">
@if($applications->isEmpty())<div class="p-4 text-center text-muted">No applications are waiting for review.</div>@else
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Reference</th><th>Student</th><th>Training</th><th>Status</th><th>Submitted</th><th></th></tr></thead><tbody>
@foreach($applications as $application)<tr><td class="font-weight-bold">{{ $application->reference_number }}</td><td>{{ $application->student->full_name }}</td><td>{{ $application->applicationWindow->trainingType->name }}</td><td><span class="badge badge-primary">{{ str_replace('_', ' ', $application->status) }}</span></td><td>{{ $application->submitted_at?->format('d M Y, H:i') ?? '—' }}</td><td class="text-right"><a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Review</a></td></tr>@endforeach
</tbody></table></div>@endif
</div></div>
@endsection
