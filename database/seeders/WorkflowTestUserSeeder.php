<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class WorkflowTestUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('app.workflow_test_password');

        if (blank($password)) {
            throw new \RuntimeException('Set APP_WORKFLOW_TEST_PASSWORD in .env before running WorkflowTestUserSeeder.');
        }

        $users = [
            ['fullname'=>'Workflow Secretary','username'=>'workflow.secretary','email'=>'secretary@practilink.test','role'=>'secretary'],
            ['fullname'=>'Workflow HOD','username'=>'workflow.hod','email'=>'hod@practilink.test','role'=>'hod'],
            ['fullname'=>'Workflow HR Officer','username'=>'workflow.hr','email'=>'hr@practilink.test','role'=>'hr'],
            ['fullname'=>'Workflow Placement Officer','username'=>'workflow.placement','email'=>'placement@practilink.test','role'=>'placement-officer'],
            ['fullname'=>'Workflow Supervisor','username'=>'workflow.supervisor','email'=>'supervisor@practilink.test','role'=>'supervisor'],
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
