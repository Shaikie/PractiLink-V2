@extends('layouts.admin')

@section('title', 'Organizations')
@section('page_title', 'Host organizations')
@section('page_description', 'Manage organizations available for student placement allocations.')

@section('content')
    <x-ui.validation-summary class="mb-3" />

    <div class="row">
        <div class="col-xl-4 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Add organization</h3></div>
                <form method="POST" action="{{ route('admin.organizations.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="organization-name">Name</label>
                            <input id="organization-name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="organization-code">Code</label>
                            <input id="organization-code" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="organization-email">Email</label>
                                <input id="organization-email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="organization-phone">Phone</label>
                                <input id="organization-phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="organization-address">Address</label>
                            <textarea id="organization-address" name="address" rows="3" class="form-control">{{ old('address') }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus" aria-hidden="true"></i> Add organization</button></div>
                </form>
            </div>
        </div>

        <div class="col-xl-8 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Organization directory</h3></div>
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.organizations.index') }}" class="pl-filter-form">
                        <div class="form-group mb-2">
                            <label for="organization-search">Search</label>
                            <div class="pl-input-wrap"><i class="fas fa-magnifying-glass" aria-hidden="true"></i><input id="organization-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Name, code or email"></div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="organization-status">Status</label>
                            <select id="organization-status" name="status" class="form-control"><option value="">All statuses</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option></select>
                        </div>
                        <div class="pl-filter-actions"><button type="submit" class="btn btn-primary">Filter</button>@if(array_filter($filters))<a href="{{ route('admin.organizations.index') }}" class="btn btn-light">Reset</a>@endif</div>
                    </form>
                </div>

                @if($organizations->isEmpty())
                    <div class="card-body"><x-ui.empty-state icon="fa-building" title="No organizations found" message="Add a host organization to make it available for placement allocation." compact /></div>
                @else
                    <div class="table-responsive">
                        <table class="table clay-table mb-0">
                            <thead><tr><th>Organization</th><th>Code</th><th>Contact</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                            <tbody>
                                @foreach($organizations as $organization)
                                    <tr>
                                        <td><strong>{{ $organization->name }}</strong><span class="small text-muted d-block">{{ $organization->address ?: 'No address provided' }}</span></td>
                                        <td>{{ $organization->code ?: '—' }}</td>
                                        <td><span class="d-block">{{ $organization->email ?: '—' }}</span><span class="small text-muted">{{ $organization->phone ?: '' }}</span></td>
                                        <td><x-ui.status-badge :status="$organization->is_active ? 'Active' : 'Inactive'" :tone="$organization->is_active ? 'success' : 'neutral'" /></td>
                                        <td class="text-right"><button class="btn btn-sm btn-light" type="button" data-toggle="collapse" data-target="#organization-{{ $organization->id }}" aria-expanded="false">Edit</button></td>
                                    </tr>
                                    <tr class="collapse" id="organization-{{ $organization->id }}">
                                        <td colspan="5">
                                            <form method="POST" action="{{ route('admin.organizations.update', $organization) }}" class="pl-inline-edit-form">
                                                @csrf @method('PUT')
                                                <div class="form-row">
                                                    <div class="form-group col-md-4"><label for="org-name-{{ $organization->id }}">Name</label><input id="org-name-{{ $organization->id }}" name="name" value="{{ old('name', $organization->name) }}" class="form-control" required></div>
                                                    <div class="form-group col-md-2"><label for="org-code-{{ $organization->id }}">Code</label><input id="org-code-{{ $organization->id }}" name="code" value="{{ old('code', $organization->code) }}" class="form-control"></div>
                                                    <div class="form-group col-md-3"><label for="org-email-{{ $organization->id }}">Email</label><input id="org-email-{{ $organization->id }}" type="email" name="email" value="{{ old('email', $organization->email) }}" class="form-control"></div>
                                                    <div class="form-group col-md-3"><label for="org-phone-{{ $organization->id }}">Phone</label><input id="org-phone-{{ $organization->id }}" name="phone" value="{{ old('phone', $organization->phone) }}" class="form-control"></div>
                                                </div>
                                                <div class="form-group"><label for="org-address-{{ $organization->id }}">Address</label><textarea id="org-address-{{ $organization->id }}" name="address" rows="2" class="form-control">{{ old('address', $organization->address) }}</textarea></div>
                                                <div class="d-flex align-items-center justify-content-between gap-3"><div class="form-check mb-0"><input id="org-active-{{ $organization->id }}" type="checkbox" name="is_active" value="1" class="form-check-input" @checked($organization->is_active)><label class="form-check-label" for="org-active-{{ $organization->id }}">Active for placement allocation</label></div><button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-save" aria-hidden="true"></i> Save changes</button></div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($organizations->hasPages())<div class="px-3 py-3 border-top">{{ $organizations->links() }}</div>@endif
                @endif
            </div>
        </div>
    </div>
@endsection
