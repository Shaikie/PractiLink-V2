<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminRoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', ['roles'=>Role::with('permissions')->withCount('users')->orderBy('name')->get(), 'permissions'=>Permission::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data=$request->validate(['name'=>['required','string','max:100'],'slug'=>['nullable','string','max:100','alpha_dash','unique:roles,slug'],'description'=>['nullable','string','max:1000'],'permissions'=>['nullable','array'],'permissions.*'=>['integer','exists:permissions,id']]);
        $slug=$data['slug'] ?: Str::slug($data['name']);
        if (Role::where('slug',$slug)->exists()) return back()->withErrors(['slug'=>'A role with this slug already exists.'])->withInput();
        $role=Role::create(['name'=>$data['name'],'slug'=>$slug,'description'=>$data['description'] ?? null]);
        $role->permissions()->sync($data['permissions'] ?? []);
        return back()->with('success','Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $data=$request->validate(['name'=>['required','string','max:100'],'slug'=>['required','string','max:100','alpha_dash',Rule::unique('roles','slug')->ignore($role->id)],'description'=>['nullable','string','max:1000'],'permissions'=>['nullable','array'],'permissions.*'=>['integer','exists:permissions,id']]);
        DB::transaction(function () use ($role,$data) {
            $isAdministrator=$role->slug==='administrator';
            $role->update(['name'=>$data['name'],'slug'=>$isAdministrator ? 'administrator' : $data['slug'],'description'=>$data['description'] ?? null]);
            if (!$isAdministrator) $role->permissions()->sync($data['permissions'] ?? []);
            else foreach (Permission::pluck('id') as $permissionId) $role->permissions()->syncWithoutDetaching($permissionId);
        });
        return back()->with('success','Role updated successfully.');
    }
}
