<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminStaffController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('fullname')->paginate(15);

        return view('admin.staff.index', ['staff' => $users]);
    }

    public function create()
    {
        return view('admin.staff.form', ['staff' => new User, 'roles' => Role::orderBy('name')->get(), 'editing' => false]);
    }

    public function store(Request $request)
    {
        $data = $this->validateStaff($request);
        $data['email'] = strtolower(trim($data['email']));
        if (Student::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'That email address is already registered to a student account.',
            ]);
        }
        $this->ensureMayAssignRoles($request, $data['roles'] ?? []);
        $user = User::create(['fullname' => $data['fullname'], 'email' => $data['email'], 'username' => $data['username'], 'phone' => $data['phone'] ?? null, 'password' => $data['password'], 'is_active' => $data['is_active'] ?? true]);
        $this->syncAccess($user, $data);

        return redirect()->route('admin.staff.index')->with('success', 'Staff account created successfully.');
    }

    public function edit(User $staff)
    {
        return view('admin.staff.form', ['staff' => $staff->load('roles'), 'roles' => Role::orderBy('name')->get(), 'editing' => true]);
    }

    public function update(Request $request, User $staff)
    {
        $data = $this->validateStaff($request, $staff);
        $data['email'] = strtolower(trim($data['email']));
        if (Student::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'That email address is already registered to a student account.',
            ]);
        }
        $this->ensureMayAssignRoles($request, $data['roles'] ?? []);
        $adminRoleId = Role::where('slug', 'administrator')->value('id');
        $retainsAdministrator = in_array($adminRoleId, $data['roles'] ?? [], true);
        if ($staff->is($request->user()) && (! $retainsAdministrator || empty($data['is_active']))) {
            throw ValidationException::withMessages(['roles' => 'You cannot remove or disable your own administrator access.']);
        }
        $currentlyAdministrator = $staff->roles()->where('slug', 'administrator')->exists();
        if ($currentlyAdministrator && (! $retainsAdministrator || empty($data['is_active']))) {
            $otherActiveAdmins = User::where('id', '!=', $staff->id)->where('is_active', true)->whereHas('roles', fn ($q) => $q->where('slug', 'administrator'))->exists();
            if (! $otherActiveAdmins) {
                throw ValidationException::withMessages(['roles' => 'At least one active administrator account must remain.']);
            }
        }
        $updates = [
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'username' => $data['username'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? false,
        ];
        if (filled($data['password'] ?? null)) {
            $updates['password'] = $data['password'];
            $updates['remember_token'] = Str::random(60);
        }
        $staff->update($updates);
        $this->syncAccess($staff, $data);

        return redirect()->route('admin.staff.index')->with('success', 'Staff account updated successfully.');
    }

    private function ensureMayAssignRoles(Request $request, array $roleIds): void
    {
        $administratorRoleId = Role::where('slug', 'administrator')->value('id');
        $assignsAdministrator = $administratorRoleId !== null && in_array($administratorRoleId, $roleIds, true);

        abort_unless(
            ! $assignsAdministrator || $request->user()?->roles()->where('slug', 'administrator')->exists(),
            403,
            'Only an administrator can assign the administrator role.',
        );
    }

    private function validateStaff(Request $request, ?User $staff = null): array
    {
        return $request->validate(['fullname' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff?->id)], 'username' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($staff?->id)], 'phone' => ['nullable', 'string', 'max:30'], 'password' => [$staff ? 'nullable' : 'required', 'string', 'min:12', 'max:72'], 'is_active' => ['nullable', 'boolean'], 'roles' => ['nullable', 'array'], 'roles.*' => ['integer', 'exists:roles,id']]);
    }

    private function syncAccess(User $user, array $data): void
    {
        $user->roles()->sync($data['roles'] ?? []);
    }
}
