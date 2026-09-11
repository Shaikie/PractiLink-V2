@extends('layouts.admin')
@section('title','Create Workflow')
@section('page_title','Create New Workflow')
@section('page_description','Set up the workflow definition. You can configure its stages and routing after creation.')
@section('content')
<div class="clay-page-narrow">
    <form method="POST" action="{{ route('admin.workflows.store') }}" class="clay-card">
        @csrf
        <div class="clay-card-body">
            <div class="clay-section-heading"><span class="clay-icon"><i class="fas fa-project-diagram"></i></span><div><h3>Workflow details</h3><p>Keep this information simple. The workflow version is configured next.</p></div></div>
            <div class="form-row">
                <div class="form-group col-md-8"><label>Name</label><input name="name" value="{{ old('name') }}" class="form-control" placeholder="e.g. Practical Training Approval" required>@error('name')<small class="text-danger">{{ $message }}</small>@enderror</div>
                <div class="form-group col-md-4"><label>Code</label><input name="code" value="{{ old('code') }}" class="form-control" placeholder="practical_training" required>@error('code')<small class="text-danger">{{ $message }}</small>@enderror</div>
            </div>
            <div class="form-group"><label>Training type <span class="font-weight-normal text-muted">(optional)</span></label><select name="training_type_id" class="custom-select"><option value="">All training types</option>@foreach($trainingTypes as $type)<option value="{{ $type->id }}" @selected(old('training_type_id')==$type->id)>{{ $type->name }}</option>@endforeach</select></div>
            <div class="form-group mb-0"><label>Description <span class="font-weight-normal text-muted">(optional)</span></label><textarea name="description" rows="4" class="form-control" placeholder="Describe when this workflow should be used.">{{ old('description') }}</textarea></div>
        </div>
        <div class="clay-card-footer"><a href="{{ route('admin.workflows.index') }}" class="btn btn-light clay-btn-secondary">Cancel</a><button class="btn btn-primary clay-btn-primary"><i class="fas fa-plus mr-1"></i>Create Workflow</button></div>
    </form>
</div>
@endsection
