@extends('layouts.admin')
@section('title',$window->exists?'Edit Application Window':'New Application Window')
@section('page_title',$window->exists?'Edit Application Window':'New Application Window')
@section('content')<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Window details</h3>
            </div>
            <form method="POST" action="{{ $window->exists?route('admin.application-windows.update',$window):route('admin.application-windows.store') }}">@csrf @if($window->exists) @method('PUT') @endif<div class="card-body">
                    @if($errors->any())<div class="alert alert-danger">
                        <ul class="mb-0 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>@endif
                    <div class="form-group"><label for="name">Name</label><input id="name" name="name" class="form-control" value="{{ old('name',$window->name) }}" maxlength="255" required></div>
                    <div class="form-group"><label for="training_type_id">Training type</label><select id="training_type_id" name="training_type_id" class="form-control" required>
                            <option value="">Select training type</option>@foreach($trainingTypes as $type)<option value="{{ $type->id }}" @selected((string)old('training_type_id',$window->training_type_id)===(string)$type->id)>{{ $type->name }}</option>@endforeach
                        </select></div>
                    <div class="row datetime-range-group">
                        <div class="col-md-6 form-group"><label for="opens_at">Opens</label><input id="opens_at" type="text" name="opens_at" class="form-control" data-datetime-start data-min-date="{{ now()->format('Y-m-d H:i') }}" value="{{ old('opens_at',$window->opens_at?->format('Y-m-d\\TH:i')) }}" placeholder="Select opening date and time" autocomplete="off" required><small class="form-text text-muted">Must be now or later for a new window.</small></div>
                        <div class="col-md-6 form-group"><label for="closes_at">Closes</label><input id="closes_at" type="text" name="closes_at" class="form-control" data-datetime-end value="{{ old('closes_at',$window->closes_at?->format('Y-m-d\\TH:i')) }}" placeholder="Select closing date and time" autocomplete="off" required><small class="form-text text-muted">Must be at least 30 minutes after opening.</small></div>
                    </div>
                    <div class="form-check"><input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active',$window->exists?$window->is_active:true))><label for="is_active" class="form-check-label">Active</label></div>
                </div>
                <div class="card-footer d-flex justify-content-between"><a href="{{ route('admin.application-windows.index') }}" class="btn btn-light border">Cancel</a><button class="btn btn-primary">Save window</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
