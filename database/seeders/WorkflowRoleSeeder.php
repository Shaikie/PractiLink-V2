<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->updateOrInsert(
            ['slug'=>'cto'],
            ['name'=>'CTO','description'=>'Final placement allocation and supervisor assignment.','updated_at'=>now(),'created_at'=>now()],
        );

        $role = Role::where('slug','cto')->firstOrFail();
        $permissionIds = DB::table('permissions')->whereIn('slug', [
            'applications.view',
            'applications.review',
            'applications.return',
            'applications.documents',
            'applications.assign_supervisor',
            'placements.manage',
        ])->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('role_permissions')->updateOrInsert([
                'role_id'=>$role->id,
                'permission_id'=>$permissionId,
            ]);
        }
    }
}
