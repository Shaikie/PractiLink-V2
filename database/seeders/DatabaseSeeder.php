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
            AdminUserSeeder::class,
            WorkflowStaffSeeder::class,
            WorkflowDepartmentSeeder::class,
            WorkflowSeeder::class,
            ApplicationWindowSeeder::class,
            DemoDataSeeder::class,
            SampleApplicationSeeder::class,

        ]);
    }
}
