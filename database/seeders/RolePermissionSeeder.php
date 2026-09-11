<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions=[['name'=>'Manage users','slug'=>'users.manage'],['name'=>'Manage students','slug'=>'students.manage'],['name'=>'Manage application windows','slug'=>'applications.manage'],['name'=>'Review applications','slug'=>'applications.review'],['name'=>'Manage workflows','slug'=>'workflows.manage'],['name'=>'Manage organizations','slug'=>'organizations.manage'],['name'=>'Manage placements','slug'=>'placements.manage'],['name'=>'Manage reports','slug'=>'reports.manage']];
        foreach($permissions as $permission) DB::table('permissions')->updateOrInsert(['slug'=>$permission['slug']],array_merge($permission,['created_at'=>now(),'updated_at'=>now()]));
        $roles=[['name'=>'Administrator','slug'=>'administrator','description'=>'Full system access.'],['name'=>'Placement Officer','slug'=>'placement-officer','description'=>'Manages student placement activities.'],['name'=>'Supervisor','slug'=>'supervisor','description'=>'Supervises student training.']];
        foreach($roles as $role) DB::table('roles')->updateOrInsert(['slug'=>$role['slug']],array_merge($role,['created_at'=>now(),'updated_at'=>now()]));
        $administrator=DB::table('roles')->where('slug','administrator')->value('id'); foreach(DB::table('permissions')->pluck('id') as $permissionId) DB::table('role_permissions')->updateOrInsert(['role_id'=>$administrator,'permission_id'=>$permissionId]);
    }
}
