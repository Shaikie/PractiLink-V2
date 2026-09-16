@extends('layouts.admin')

@section('title', 'Application Windows')
@section('page_title', 'Application Windows')
@section('page_description', 'Control when students can submit practical training applications.')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Configured windows</h3>
        <a href="{{ route('admin.application-windows.create') }}" class="btn btn-primary btn-sm ml-auto">
            <i class="fas fa-plus mr-1"></i> New window</a>
    </div>
    <div class="card-body p-0">
        @if ($windows->isEmpty())
        <div class="p-4 text-center text-muted">No application windows have been configured.</div>
        @else
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Training type</th>
                        <th>Opens</th>
                        <th>Closes</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($windows as $window)
                    <tr>
                        <td class="font-weight-bold">{{ $window->name }}</td>
                        <td>{{ $window->trainingType->name }}</td>
                        <td>{{ $window->opens_at->format('d M Y, H:i') }}</td>
                        <td>{{ $window->closes_at->format('d M Y, H:i') }}</td>
                        <td><span class="badge badge-{{ $window->isOpen() ? 'success' : 'primary' }}">{{ $window->isOpen() ? 'Open' : ($window->is_active ? 'Closed' : 'Inactive') }}</span></td>
                        <td class="text-right"><a href="{{ route('admin.application-windows.edit', $window) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.application-windows.destroy', $window) }}" class="d-inline ml-1">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this application window?')">Delete</button></form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($windows->hasPages())<div class="p-3 border-top">{{ $windows->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection