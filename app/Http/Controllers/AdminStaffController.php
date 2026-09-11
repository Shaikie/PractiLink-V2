<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    public function index()
    {
        return view('admin.staff.index', ['staff'=>User::with(['roles','permissions'])->orderBy('fullname')->get()]);
    }

    public function create()
    {
        return view('admin.staff.form', ['staff'=>new User(), 'roles'=>Role::orderBy('name')->get(), 'permissions'=>Permission::orderBy('name')->get(), 'editing'=>false]);
    }

    public function store(Request $request)
    {
        $data = $this->validateStaff($request);
        $user = User::create([
            'fullname'=>$data['fullname'], 'email'=>$data['email'], 'username'=>$data['username'],
            'phone'=>$data['phone'] ?? null, 'password'=>$data['password'], 'is_active'=>$data['is_active'] ?? true,
        ]);
        $this->syncAccess($user, $data);
        return redirect()->route('admin.staff.index')->with('success','Staff account created successfully.');
    }

    public function edit(User $staff)
    {
        return view('admin.staff.form', ['staff'=>$staff->load(['roles','permissions']), 'roles'=>Role::orderBy('name')->get(), 'permissions'=>Permission::orderBy('name')->get(), 'editing'=>true]);
    }

    public function update(Request $request, User $staff)
    {
        $data = $this->validateStaff($request, $staff);
        $staff->update([
            'fullname'=>$data['fullname'], 'email'=>$data['email'], 'username'=>$data['username'],
            'phone'=>$data['phone'] ?? null, 'is_active'=>$data['is_active'] ?? false,
        ] + (filled($data['password'] ?? null) ? ['password'=>$data['password']] : []));
        $this->syncAccess($staff, $data);
        return redirect()->route('admin.staff.index')->with('success','Staff account updated successfully.');
    }

    private function validateStaff(Request $request, ?User $staff = null): array
    {
        return $request->validate([
            'fullname'=>['required','string','max:255'],
            'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($staff?->id)],
            'username'=>['required','string','max:100','alpha_dash',Rule::unique('users','username')->ignore($staff?->id)],
            'phone'=>['nullable','string','max:30'],
            'password'=>[$staff ? 'nullable' : 'required','string','min:12','max:72'],
            'is_active'=>['nullable','boolean'],
            'roles'=>['nullable','array'], 'roles.*'=>['integer','exists:roles,id'],
            'permissions'=>['nullable','array'], 'permissions.*'=>['integer','exists:permissions,id'],
        ]);
    }

    private function syncAccess(User $user, array $data): void
    {
        $user->roles()->sync($data['roles'] ?? []);
        $user->permissions()->sync($data['permissions'] ?? []);
    }
}
