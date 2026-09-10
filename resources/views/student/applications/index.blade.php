@extends('layouts.admin')

@section('title', 'My Applications')
@section('page_title', 'My Applications')
@section('page_description', 'Create and track your practical training applications.')

@section('content')
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Application history</h3></div>
            <div class="card-body p-0">
                @if ($applications->isEmpty())
                    <div class="p-4 text-center text-muted"><i class="fas fa-folder-open fa-2x mb-3"></i><p class="mb-0">You have no applications yet.</p></div>
                @else
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Reference</th><th>Training</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            @foreach ($applications as $application)
                                <tr>
                                    <td class="font-weight-bold">{{ $application->reference_number }}</td>
                                    <td>{{ $application->applicationWindow->trainingType->name }}</td>
                                    <td><span class="badge badge-{{ in_array($application->status, ['ACCEPTED'], true) ? 'success' : 'primary' }}">{{ str_replace('_', ' ', $application->status) }}</span></td>
                                    <td class="text-right"><a href="{{ route('student.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Open application windows</h3></div>
            <div class="card-body">
                @forelse ($windows as $window)
                    <div class="border rounded p-3 mb-3">
                        <div class="font-weight-bold">{{ $window->name }}</div>
                        <div class="small text-muted mb-2">{{ $window->trainingType->name }} · closes {{ $window->closes_at->format('d M Y, H:i') }}</div>
                        <form method="POST" action="{{ route('student.applications.store') }}">
                            @csrf
                            <input type="hidden" name="application_window_id" value="{{ $window->id }}">
                            <button class="btn btn-primary btn-sm btn-block" type="submit">Start application</button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted mb-0">There are no open application windows right now.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
