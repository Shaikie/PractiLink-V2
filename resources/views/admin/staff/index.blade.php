@extends('layouts.admin')
@section('title','Staff')
@section('page_title','Staff Management')
@section('page_description','Create staff accounts and control their roles and permissions.')
@section('content')
<div class="card"><div class="card-header d-flex align-items-center"><h3 class="card-title">Staff accounts</h3><a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm ml-auto">Create staff</a></div><div class="card-body p-0"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th></th></tr></thead><tbody>@forelse($staff as $member)<tr><td><strong>{{ $member->fullname }}</strong><div class="small text-muted">{{ $member->username }}</div></td><td>{{ $member->email }}</td><td>@foreach($member->roles as $role)<span class="badge badge-light mr-1">{{ $role->name }}</span>@endforeach</td><td><span class="badge badge-{{ $member->is_active?'success':'secondary' }}">{{ $member->is_active?'Active':'Inactive' }}</span></td><td class="text-right"><a href="{{ route('admin.staff.edit',$member) }}" class="btn btn-sm btn-outline-primary">Edit</a></td></tr>@empty<tr><td colspan="5" class="text-center text-muted p-4">No staff accounts.</td></tr>@endforelse</tbody></table></div></div></div>
@endsection
