<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_access_is_configured_through_roles_only(): void
    {
        $manageUsers = Permission::create(['name' => 'Manage users', 'slug' => 'users.manage']);
        $role = Role::create(['name' => 'Administrator', 'slug' => 'administrator']);
        $role->permissions()->attach($manageUsers);
        $administrator = User::factory()->create();
        $administrator->roles()->attach($role);

        $staffRole = Role::create(['name' => 'Reviewer', 'slug' => 'reviewer']);
        $rolePermission = Permission::create(['name' => 'Review applications', 'slug' => 'applications.review']);
        $directPermission = Permission::create(['name' => 'Manage reports', 'slug' => 'reports.manage']);
        $staffRole->permissions()->attach($rolePermission);

        $this->actingAs($administrator)
            ->get(route('admin.staff.create'))
            ->assertOk()
            ->assertSee('Roles')
            ->assertDontSee('Direct permissions');

        $this->actingAs($administrator)
            ->post(route('admin.staff.store'), [
                'fullname' => 'New Reviewer',
                'email' => 'reviewer@example.com',
                'username' => 'new-reviewer',
                'password' => 'Password123!abc',
                'is_active' => true,
                'roles' => [$staffRole->id],
                'permissions' => [$directPermission->id],
            ])
            ->assertRedirect(route('admin.staff.index'));

        $staff = User::where('username', 'new-reviewer')->firstOrFail();

        $this->assertDatabaseHas('user_roles', ['user_id' => $staff->id, 'role_id' => $staffRole->id]);
        $this->assertDatabaseMissing('permission_user', ['user_id' => $staff->id, 'permission_id' => $directPermission->id]);
        $this->assertTrue($staff->hasPermission('applications.review'));
        $this->assertFalse($staff->hasPermission('reports.manage'));
    }
}
