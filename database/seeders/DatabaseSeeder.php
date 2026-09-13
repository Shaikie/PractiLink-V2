<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            ReferenceDataSeeder::class,
            RolePermissionSeeder::class,
            WorkflowRoleSeeder::class,
            WorkflowDepartmentSeeder::class,
            AdminUserSeeder::class,
            WorkflowSeeder::class,
        ]);

        if (config('app.workflow_test_password')) {
            $this->call(WorkflowStaffSeeder::class);
            $this->call(WorkflowDepartmentSeeder::class);
        }
    }
}
