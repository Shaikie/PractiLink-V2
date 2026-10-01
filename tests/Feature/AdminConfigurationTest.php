<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuration_dashboard_requires_user_management_permission(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.reference-data.index'))
            ->assertForbidden();
    }

    public function test_authorized_staff_can_add_and_update_reference_data(): void
    {
        $this->actingAs($this->administrator())
            ->get(route('admin.reference-data.departments.index'))
            ->assertOk()
            ->assertSee('Add department');

        $this->post(route('admin.reference-data.departments.store'), [
            'name' => 'Quality Assurance',
            'code' => 'QA',
        ])->assertRedirect();

        $this->assertDatabaseHas('departments', ['name' => 'Quality Assurance', 'code' => 'QA']);

        $department = Department::where('name', 'Quality Assurance')->firstOrFail();

        $this->put(route('admin.reference-data.departments.update', $department), [
            'name' => 'Quality and Assurance',
            'code' => 'QAS',
        ])->assertRedirect();

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Quality and Assurance',
            'code' => 'QAS',
        ]);
    }

    public function test_organization_can_be_created_and_deactivated_by_authorized_staff(): void
    {
        $this->actingAs($this->administrator())
            ->post(route('admin.organizations.store'), [
                'name' => 'Northstar Engineering',
                'code' => 'NORTHSTAR',
                'email' => 'placement@northstar.test',
            ])
            ->assertRedirect();

        $organization = Organization::where('name', 'Northstar Engineering')->firstOrFail();

        $this->put(route('admin.organizations.update', $organization), [
            'name' => $organization->name,
            'code' => $organization->code,
            'is_active' => false,
        ])->assertRedirect();

        $this->assertFalse($organization->fresh()->is_active);
    }

    public function test_document_type_rejects_a_minimum_larger_than_its_maximum(): void
    {
        $this->actingAs($this->administrator())
            ->post(route('admin.reference-data.document-types.store'), [
                'name' => 'Training Passport',
                'code' => 'PASSPORT',
                'min_size_kb' => 100,
                'max_size_kb' => 50,
            ])
            ->assertSessionHasErrors('min_size_kb');

        $this->assertDatabaseMissing('document_types', ['code' => 'PASSPORT']);
    }

    private function administrator(): User
    {
        $permissions = [
            Permission::create(['name' => 'Manage users', 'slug' => 'users.manage']),
            Permission::create(['name' => 'Manage organizations', 'slug' => 'organizations.manage']),
            Permission::create(['name' => 'Manage students', 'slug' => 'students.manage']),
        ];
        $role = Role::create(['name' => 'Administrator', 'slug' => 'administrator']);
        $role->permissions()->attach(collect($permissions)->pluck('id')->all());
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
