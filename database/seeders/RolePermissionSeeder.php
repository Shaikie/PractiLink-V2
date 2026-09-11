<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions=[
            ['name'=>'View applications','slug'=>'applications.view'],
            ['name'=>'Review applications','slug'=>'applications.review'],
            ['name'=>'Forward applications','slug'=>'applications.forward'],
            ['name'=>'Return applications for correction','slug'=>'applications.return'],
            ['name'=>'Reject applications','slug'=>'applications.reject'],
            ['name'=>'Accept applications','slug'=>'applications.accept'],
            ['name'=>'Assign supervisors','slug'=>'applications.assign_supervisor'],
            ['name'=>'Create placements','slug'=>'placements.create'],
            ['name'=>'Manage application windows','slug'=>'applications.manage'],
            ['name'=>'Manage workflows','slug'=>'workflows.manage'],
            ['name'=>'Manage staff','slug'=>'staff.manage'],
            ['name'=>'Manage roles and permissions','slug'=>'roles.manage'],
            ['name'=>'Manage organizations','slug'=>'organizations.manage'],
            ['name'=>'Manage placements','slug'=>'placements.manage'],
            ['name'=>'Manage reports','slug'=>'reports.manage'],
            ['name'=>'Act on Secretary Review stage','slug'=>'workflow.secretary_review'],
            ['name'=>'Act on HOD Review stage','slug'=>'workflow.hod_review'],
            ['name'=>'Act on HR Review stage','slug'=>'workflow.hr_review'],
            ['name'=>'Act on Placement stage','slug'=>'workflow.placement'],
        ];
        foreach($permissions as $permission) DB::table('permissions')->updateOrInsert(['slug'=>$permission['slug']],array_merge($permission,['created_at'=>now(),'updated_at'=>now()]));

        $roles=[
            ['name'=>'Administrator','slug'=>'administrator','description'=>'Full system administration.'],
            ['name'=>'Secretary','slug'=>'secretary','description'=>'Reviews, forwards and returns applications.'],
            ['name'=>'HOD','slug'=>'hod','description'=>'Reviews applications at departmental level.'],
            ['name'=>'HR','slug'=>'hr','description'=>'Reviews and processes applications through HR.'],
            ['name'=>'Placement Officer','slug'=>'placement-officer','description'=>'Manages placement and supervisor assignment.'],
            ['name'=>'Supervisor','slug'=>'supervisor','description'=>'Supervises assigned students.'],
        ];
        foreach($roles as $role) DB::table('roles')->updateOrInsert(['slug'=>$role['slug']],array_merge($role,['created_at'=>now(),'updated_at'=>now()]));

        $permissionIds=DB::table('permissions')->pluck('id','slug');
        $roleIds=DB::table('roles')->pluck('id','slug');
        foreach($permissionIds as $permissionId) DB::table('role_permissions')->updateOrInsert(['role_id'=>$roleIds['administrator'],'permission_id'=>$permissionId]);

        $rolePermissions=[
            'secretary'=>['applications.view','applications.review','applications.forward','applications.return','workflow.secretary_review'],
            'hod'=>['applications.view','applications.review','applications.forward','applications.return','applications.reject','applications.accept','workflow.hod_review'],
            'hr'=>['applications.view','applications.review','applications.forward','applications.return','applications.reject','applications.accept','workflow.hr_review'],
            'placement-officer'=>['applications.view','applications.review','applications.forward','applications.return','applications.assign_supervisor','applications.accept','placements.create','placements.manage','workflow.placement'],
            'supervisor'=>['applications.view'],
        ];
        foreach($rolePermissions as $roleSlug=>$slugs){
            foreach($slugs as $slug){ if(isset($permissionIds[$slug])) DB::table('role_permissions')->updateOrInsert(['role_id'=>$roleIds[$roleSlug],'permission_id'=>$permissionIds[$slug]]); }
        }
    }
}
