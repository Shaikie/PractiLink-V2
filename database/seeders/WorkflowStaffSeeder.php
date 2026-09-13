<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowStaffSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('app.workflow_test_password');

        if (blank($password)) {
            throw new \RuntimeException('Set APP_WORKFLOW_TEST_PASSWORD in .env before seeding workflow staff.');
        }

        DB::table('roles')->updateOrInsert(
            ['slug' => 'cto'],
            ['name' => 'CTO', 'description' => 'Final placement allocation and supervisor assignment.', 'updated_at' => now(), 'created_at' => now()],
        );

        $users = [
            ['fullname'=>'Workflow Secretary','username'=>'workflow.secretary','email'=>'secretary@practilink.test','role'=>'secretary'],
            ['fullname'=>'Workflow HR Officer','username'=>'workflow.hr','email'=>'hr@practilink.test','role'=>'hr'],
            ['fullname'=>'Workflow CTO','username'=>'workflow.cto','email'=>'cto@practilink.test','role'=>'cto'],
            ['fullname'=>'Workflow HOD - Computer Science','username'=>'workflow.hod.cs','email'=>'hod.cs@practilink.test','role'=>'hod'],
            ['fullname'=>'Workflow HOD - Information Technology','username'=>'workflow.hod.it','email'=>'hod.it@practilink.test','role'=>'hod'],
            ['fullname'=>'Workflow HOD - Electronics and Telecommunications','username'=>'workflow.hod.ect','email'=>'hod.ect@practilink.test','role'=>'hod'],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'fullname' => $data['fullname'],
                    'username' => $data['username'],
                    'phone' => null,
                    'password' => $password,
                    'is_active' => true,
                ],
            );

            $role = Role::where('slug', $data['role'])->firstOrFail();
            $user->roles()->sync([$role->id]);
        }
    }
}
