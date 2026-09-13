<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class WorkflowDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name'=>'Computer Science','code'=>'CS','hod'=>'hod.cs@practilink.test'],
            ['name'=>'Information Technology','code'=>'IT','hod'=>'hod.it@practilink.test'],
            ['name'=>'Electronics and Telecommunications','code'=>'ECT','hod'=>'hod.ect@practilink.test'],
        ];

        foreach ($departments as $data) {
            $department = Department::updateOrCreate(['code'=>$data['code']], ['name'=>$data['name']]);
            $hod = User::where('email',$data['hod'])->first();
            if ($hod) {
                $department->users()->syncWithoutDetaching([$hod->id]);
            }
        }
    }
}
