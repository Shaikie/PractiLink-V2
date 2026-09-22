@extends('layouts.admin')
@section('title',$editing?'Edit Staff':'Add Staff')
@section('page_title',$editing?'Edit Staff':'Add Staff')
@section('page_description','Configure account status and roles.')
@section('content')
@if($errors->any())<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>@endif
<form method="POST" action="{{ $editing ? route('admin.staff.update',$staff) : route('admin.staff.store') }}">@csrf @if($editing) @method('PUT') @endif
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Account</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6"><label>Full name</label><input name="fullname" class="form-control" value="{{ old('fullname',$staff->fullname) }}" required></div>
                <div class="form-group col-md-6"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email',$staff->email) }}" required></div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4"><label>Username</label><input name="username" class="form-control" value="{{ old('username',$staff->username) }}" required></div>
                <div class="form-group col-md-4"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone',$staff->phone) }}"></div>
                <div class="form-group col-md-4"><label>Password {{ $editing?'(leave blank to keep current)':'' }}</label><input type="password" name="password" class="form-control" minlength="12" {{ $editing?'':'required' }}></div>
            </div>
            <div class="custom-control custom-switch"><input type="checkbox" name="is_active" value="1" class="custom-control-input" id="active" @checked(old('is_active',$staff->exists ? $staff->is_active : true))><label class="custom-control-label" for="active">Account active</label></div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Roles</h3>
                </div>
                <div class="card-body">@foreach($roles as $role)<div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input" name="roles[]" value="{{ $role->id }}" id="role{{ $role->id }}" @checked($staff->roles->contains($role->id))><label class="custom-control-label" for="role{{ $role->id }}"><strong>{{ $role->name }}</strong><small class="d-block text-muted">{{ $role->description }}</small></label></div>@endforeach</div>
            </div>
        </div>
    </div>
    <div class="mt-3"><a href="{{ route('admin.staff.index') }}" class="btn btn-light border">Cancel</a><button class="btn btn-primary float-right">{{ $editing?'Save changes':'Create staff account' }}</button></div>
</form>
@endsection