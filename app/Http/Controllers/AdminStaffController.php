<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    public function index()
    {
        return view('admin.staff.index', ['staff'=>User::with('roles')->orderBy('fullname')->get()]);
    }

    public function create()
    {
        return view('admin.staff.form', ['staff'=>new User, 'roles'=>Role::orderBy('name')->get(), 'permissions'=>Permission::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data=$request->validate($this->rules());
        $staff=DB::transaction(function() use($data){
            $staff=User::create(['fullname'=>$data['fullname'],'email'=>$data['email'],'username'=>$data['username'],'phone'=>$data['phone']??null,'password'=>$data['password'],'is_active'=>true]);
            $staff->roles()->sync($data['role_ids']??[]); $staff->permissions()->sync($data['permission_ids']??[]); return $staff;
        });
        return redirect()->route('admin.staff.index')->with('success',"Staff account {$staff->fullname} created.");
    }

    public function edit(User $staff)
    {
        return view('admin.staff.form', ['staff'=>$staff->load('roles','permissions'), 'roles'=>Role::orderBy('name')->get(), 'permissions'=>Permission::orderBy('name')->get()]);
    }

    public function update(Request $request, User $staff)
    {
        $data=$request->validate($this->rules($staff));
        if($staff->is($request->user()) && empty($data['is_active'])) abort(422,'You cannot deactivate your own account.');
        DB::transaction(function() use($data,$staff){
            $staff->update(['fullname'=>$data['fullname'],'email'=>$data['email'],'username'=>$data['username'],'phone'=>$data['phone']??null,'is_active'=>(bool)$data['is_active']] + (filled($data['password']??null)?['password'=>$data['password']]:[]));
            $staff->roles()->sync($data['role_ids']??[]); $staff->permissions()->sync($data['permission_ids']??[]);
        });
        return redirect()->route('admin.staff.index')->with('success','Staff account updated.');
    }

    private function rules(?User $staff=null): array
    {
        return [
            'fullname'=>['required','string','max:255'],
            'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($staff?->id)],
            'username'=>['required','string','max:100',Rule::unique('users','username')->ignore($staff?->id)],
            'phone'=>['nullable','string','max:30'],
            'password'=>[$staff?'nullable':'required','string','min:12','confirmed'],
            'is_active'=>['nullable','boolean'],
            'role_ids'=>['nullable','array'],'role_ids.*'=>['integer','exists:roles,id'],
            'permission_ids'=>['nullable','array'],'permission_ids.*'=>['integer','exists:permissions,id'],
        ];
    }
}
