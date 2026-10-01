@extends('layouts.admin')

@section('title', 'Placements')
@section('page_title', 'Placements')
@section('page_description', 'Track allocated placements and move each placement through its lifecycle.')

@section('content')
    <x-ui.validation-summary class="mb-3" />

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Placement register</h3>
                <p class="mb-0 mt-1 text-muted small">{{ $placements->total() }} {{ \Illuminate\Support\Str::plural('placement', $placements->total()) }}</p>
            </div>
        </div>

        @if($placements->isEmpty())
            <div class="card-body"><x-ui.empty-state icon="fa-map-marker-alt" title="No placements allocated" message="Accepted applications at the CTO placement stage can be allocated here." compact /></div>
        @else
            <div class="table-responsive">
                <table class="table clay-table mb-0">
                    <thead>
                        <tr><th>Reference</th><th>Student</th><th>Organization</th><th>Dates</th><th>Status</th><th class="text-right">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($placements as $placement)
                            <tr>
                                <td><strong>{{ $placement->reference_number }}</strong><span class="small text-muted d-block">{{ $placement->department?->name ?? 'Department pending' }}</span></td>
                                <td>{{ $placement->student->full_name }}</td>
                                <td>{{ $placement->organization->name }}</td>
                                <td><span class="d-block">{{ $placement->start_date->format('d M Y') }}</span><span class="small text-muted">to {{ $placement->end_date->format('d M Y') }}</span></td>
                                <td><x-ui.status-badge :status="$placement->status" /></td>
                                <td class="text-right">
                                    <div class="d-flex flex-wrap justify-content-end gap-1">
                                        <form method="POST" action="{{ route('admin.placements.letter.issue', $placement) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary" type="submit"><i class="fas fa-file-pdf" aria-hidden="true"></i> Letter</button>
                                        </form>
                                        @if($canManagePlacements && $placement->status === 'ALLOCATED')
                                            <form method="POST" action="{{ route('admin.placements.status.update', $placement) }}" data-confirm="Activate this placement?">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="status" value="ACTIVE">
                                                <button class="btn btn-sm btn-success" type="submit">Activate</button>
                                            </form>
                                        @endif
                                        @if($canManagePlacements && in_array($placement->status, ['ALLOCATED', 'ACTIVE'], true))
                                            <form method="POST" action="{{ route('admin.placements.status.update', $placement) }}" data-confirm="Cancel this placement?">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="status" value="CANCELLED">
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Cancel</button>
                                            </form>
                                        @endif
                                        @if($canManagePlacements && $placement->status === 'ACTIVE')
                                            <form method="POST" action="{{ route('admin.placements.status.update', $placement) }}" data-confirm="Mark this placement as completed?">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="status" value="COMPLETED">
                                                <button class="btn btn-sm btn-primary" type="submit">Complete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($placements->hasPages())<div class="px-3 py-3 border-top">{{ $placements->links() }}</div>@endif
        @endif
    </div>
@endsection
