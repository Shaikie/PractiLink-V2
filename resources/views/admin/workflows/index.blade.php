@extends('layouts.admin')
@section('title','Workflows')
@section('page_title','Workflows')
@section('page_description','Manage approval workflows and their versioned configurations.')
@section('page_actions')<a href="{{ route('admin.workflows.create') }}" class="btn btn-primary clay-btn-primary"><i class="fas fa-plus mr-1"></i>Create New Workflow</a>@endsection
@section('content')
@if($workflows->isEmpty())
    <div class="clay-card clay-empty-state"><div class="clay-empty-icon"><i class="fas fa-project-diagram"></i></div><h3>No workflows yet</h3><p>Create the first workflow to define how applications move through review.</p><a href="{{ route('admin.workflows.create') }}" class="btn btn-primary clay-btn-primary">Create New Workflow</a></div>
@else
<div class="clay-card"><div class="clay-table-wrap"><table class="table clay-table mb-0"><thead><tr><th>Workflow</th><th>Training type</th><th>Published</th><th>Draft</th><th>Updated</th><th class="text-right">Action</th></tr></thead><tbody>
@foreach($workflows as $workflow)
@php $published=$workflow->versions->where('status','PUBLISHED')->sortByDesc('version')->first(); $draft=$workflow->versions->where('status','DRAFT')->sortByDesc('version')->first(); @endphp
<tr><td><div class="font-weight-bold">{{ $workflow->name }}</div><div class="clay-muted small">{{ $workflow->code }}</div></td><td>{{ $workflow->trainingType?->name ?? 'All training types' }}</td><td>@if($published)<span class="badge badge-success clay-badge">v{{ $published->version }} · Published</span>@else<span class="clay-muted">—</span>@endif</td><td>@if($draft)<span class="badge badge-warning clay-badge">v{{ $draft->version }} · Draft</span>@else<span class="clay-muted">—</span>@endif</td><td>{{ $workflow->updated_at?->format('d M Y') }}</td><td class="text-right"><a href="{{ route('admin.workflows.show',$workflow) }}" class="btn btn-sm btn-outline-primary clay-btn-outline"><i class="fas fa-sliders-h mr-1"></i>Manage</a></td></tr>
@endforeach
</tbody></table></div>
@if($workflows->hasPages())<div class="p-3 border-top">{{ $workflows->links() }}</div>@endif
</div>
@endif
@endsection
