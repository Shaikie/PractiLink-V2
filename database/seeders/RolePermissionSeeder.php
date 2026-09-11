<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name'=>'Manage users','slug'=>'users.manage'],
            ['name'=>'Manage students','slug'=>'students.manage'],
            ['name'=>'Manage application windows','slug'=>'applications.manage'],
            ['name'=>'View applications','slug'=>'applications.view'],
            ['name'=>'Review applications','slug'=>'applications.review'],
            ['name'=>'Forward applications','slug'=>'applications.forward'],
            ['name'=>'Return applications for correction','slug'=>'applications.return'],
            ['name'=>'Reject applications','slug'=>'applications.reject'],
            ['name'=>'Accept applications','slug'=>'applications.accept'],
            ['name'=>'Assign supervisors','slug'=>'applications.assign_supervisor'],
            ['name'=>'Manage documents','slug'=>'applications.documents'],
            ['name'=>'Manage workflows','slug'=>'workflows.manage'],
            ['name'=>'Manage organizations','slug'=>'organizations.manage'],
            ['name'=>'Manage placements','slug'=>'placements.manage'],
            ['name'=>'Manage reports','slug'=>'reports.manage'],
        ];
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(['slug'=>$permission['slug']], array_merge($permission,['updated_at'=>now(),'created_at'=>now()]));
        }

        $roles = [
            ['name'=>'Administrator','slug'=>'administrator','description'=>'Full system administration.'],
            ['name'=>'Secretary','slug'=>'secretary','description'=>'Initial application review and routing.'],
            ['name'=>'HOD','slug'=>'hod','description'=>'Departmental application review.'],
            ['name'=>'HR','slug'=>'hr','description'=>'Human resources review and routing.'],
            ['name'=>'Placement Officer','slug'=>'placement-officer','description'=>'Placement allocation and supervisor assignment.'],
            ['name'=>'Supervisor','slug'=>'supervisor','description'=>'Student supervision activities.'],
        ];
        foreach ($roles as $role) DB::table('roles')->updateOrInsert(['slug'=>$role['slug']], array_merge($role,['updated_at'=>now(),'created_at'=>now()]));

        $permissionIds = DB::table('permissions')->pluck('id','slug');
        $all = $permissionIds->values();
        $administrator = DB::table('roles')->where('slug','administrator')->value('id');
        foreach ($all as $permissionId) DB::table('role_permissions')->updateOrInsert(['role_id'=>$administrator,'permission_id'=>$permissionId]);

        $rolePermissions = [
            'secretary' => ['applications.view','applications.review','applications.forward','applications.return','applications.documents'],
            'hod' => ['applications.view','applications.review','applications.forward','applications.return','applications.reject','applications.accept','applications.documents'],
            'hr' => ['applications.view','applications.review','applications.forward','applications.return','applications.reject','applications.documents'],
            'placement-officer' => ['applications.view','applications.forward','applications.return','applications.assign_supervisor','applications.documents','placements.manage'],
            'supervisor' => ['applications.view'],
        ];
        foreach ($rolePermissions as $roleSlug => $slugs) {
            $roleId = DB::table('roles')->where('slug',$roleSlug)->value('id');
            foreach ($slugs as $slug) if (isset($permissionIds[$slug])) DB::table('role_permissions')->updateOrInsert(['role_id'=>$roleId,'permission_id'=>$permissionIds[$slug]]);
        }
    }
}
