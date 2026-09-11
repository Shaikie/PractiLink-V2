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
        return view('admin.roles.index', ['roles'=>Role::withCount('users')->with('permissions')->orderBy('name')->get(), 'permissions'=>Permission::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data=$request->validate($this->rules());
        $role=DB::transaction(function() use($data){ $role=Role::create(['name'=>$data['name'],'slug'=>$data['slug'],'description'=>$data['description']??null]); $role->permissions()->sync($data['permission_ids']??[]); return $role; });
        return back()->with('success',"Role {$role->name} created.");
    }

    public function update(Request $request, Role $role)
    {
        $data=$request->validate($this->rules($role));
        DB::transaction(function() use($data,$role){ $role->update(['name'=>$data['name'],'slug'=>$data['slug'],'description'=>$data['description']??null]); $role->permissions()->sync($data['permission_ids']??[]); });
        return back()->with('success',"Role {$role->name} updated.");
    }

    private function rules(?Role $role=null): array
    {
        return ['name'=>['required','string','max:100'], 'slug'=>['required','string','max:100','alpha_dash',Rule::unique('roles','slug')->ignore($role?->id)], 'description'=>['nullable','string','max:1000'], 'permission_ids'=>['nullable','array'],'permission_ids.*'=>['integer','exists:permissions,id']];
    }
}
