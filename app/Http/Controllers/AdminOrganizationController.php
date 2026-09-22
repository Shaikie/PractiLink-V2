<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrganizationController extends Controller
{
    public function index()
    {
        return view('admin.organizations.index', ['organizations' => Organization::latest()->paginate(15)]);
    }
    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:organizations,name'], 'code' => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:organizations,code'], 'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'], 'address' => ['nullable', 'string', 'max:1000']]);
        $org = Organization::create($data + ['is_active' => true]);
        AuditLogger::record('organization.created', $org, null, $org->toArray());
        return back()->with('success', 'Organization added.');
    }
    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('organizations', 'name')->ignore($organization->id)], 'code' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('organizations', 'code')->ignore($organization->id)], 'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'], 'address' => ['nullable', 'string', 'max:1000'], 'is_active' => ['nullable', 'boolean']]);
        $old = $organization->toArray();
        $organization->update($data + ['is_active' => $request->boolean('is_active')]);
        AuditLogger::record('organization.updated', $organization, $old, $organization->fresh()->toArray());
        return back()->with('success', 'Organization updated.');
    }
}
