<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    private const STAGES = [
        ['name'=>'Secretary Review','code'=>'SECRETARY_REVIEW','role'=>'secretary','staff'=>'secretary@practilink.test','mode'=>'STAFF','final'=>false,'placement'=>false,'duties'=>[
            'Verify student and application details',
            'Verify all required application documents',
            'Confirm application completeness',
        ]],
        ['name'=>'HR Review','code'=>'HR_REVIEW','role'=>'hr','staff'=>'hr@practilink.test','mode'=>'STAFF','final'=>false,'placement'=>false,'duties'=>[
            'Review application against training requirements',
            'Verify the application is routed to the correct department',
            'Confirm HR compliance before departmental review',
        ]],
        ['name'=>'HOD Review','code'=>'HOD_REVIEW','role'=>'hod','staff'=>null,'mode'=>'DEPARTMENT_ROLE','final'=>false,'placement'=>false,'duties'=>[
            'Review student suitability for the selected department',
            'Confirm departmental capacity and approval',
        ]],
        ['name'=>'CTO Placement','code'=>'CTO_PLACEMENT','role'=>'cto','staff'=>'cto@practilink.test','mode'=>'STAFF','final'=>true,'placement'=>true,'duties'=>[
            'Allocate practical training placement',
            'Assign workplace supervisor',
        ]],
    ];

    private const TRANSITIONS = [
        ['from'=>'SECRETARY_REVIEW','to'=>'HR_REVIEW','action'=>'FORWARD','label'=>'Forward to HR','status'=>'UNDER_REVIEW','role'=>'secretary','comment'=>false],
        ['from'=>'SECRETARY_REVIEW','to'=>'SECRETARY_REVIEW','action'=>'RETURN','label'=>'Return to student for correction','status'=>'RETURNED','role'=>'secretary','comment'=>true],
        ['from'=>'SECRETARY_REVIEW','to'=>'SECRETARY_REVIEW','action'=>'REJECT','label'=>'Reject application','status'=>'REJECTED','role'=>'secretary','comment'=>true],
        ['from'=>'HR_REVIEW','to'=>'HOD_REVIEW','action'=>'FORWARD','label'=>'Forward to HOD','status'=>'UNDER_REVIEW','role'=>'hr','comment'=>false],
        ['from'=>'HR_REVIEW','to'=>'SECRETARY_REVIEW','action'=>'RETURN','label'=>'Return to Secretary','status'=>'RETURNED','role'=>'hr','comment'=>true],
        ['from'=>'HR_REVIEW','to'=>'HR_REVIEW','action'=>'REJECT','label'=>'Reject application','status'=>'REJECTED','role'=>'hr','comment'=>true],
        ['from'=>'HOD_REVIEW','to'=>'CTO_PLACEMENT','action'=>'ACCEPT','label'=>'Approve and forward to CTO','status'=>'ACCEPTED','role'=>'hod','comment'=>false],
        ['from'=>'HOD_REVIEW','to'=>'HR_REVIEW','action'=>'RETURN','label'=>'Return to HR','status'=>'RETURNED','role'=>'hod','comment'=>true],
        ['from'=>'HOD_REVIEW','to'=>'HOD_REVIEW','action'=>'REJECT','label'=>'Reject application','status'=>'REJECTED','role'=>'hod','comment'=>true],
        ['from'=>'CTO_PLACEMENT','to'=>'CTO_PLACEMENT','action'=>'COMPLETE_PLACEMENT','label'=>'Complete placement','status'=>'ACCEPTED','role'=>'cto','comment'=>false],
    ];

    public function run(): void
    {
        foreach (['INDUSTRIAL'=>'Industrial Training Application','INTERNSHIP'=>'Internship Application'] as $trainingCode => $name) {
            $trainingTypeId = DB::table('training_types')->where('code', $trainingCode)->value('id');
            if (!$trainingTypeId) continue;

            $definition = DB::table('workflow_definitions')->updateOrInsert(
                ['code'=>'DEFAULT_'.$trainingCode],
                ['name'=>$name,'training_type_id'=>$trainingTypeId,'description'=>'Secretary → HR → Department HOD → CTO placement workflow.','is_active'=>true,'updated_at'=>now(),'created_at'=>now()]
            );
            $definitionId = DB::table('workflow_definitions')->where('code','DEFAULT_'.$trainingCode)->value('id');
            $versionId = DB::table('workflow_versions')->where('workflow_definition_id',$definitionId)->where('version',1)->value('id');

            if (!$versionId) {
                $versionId = DB::table('workflow_versions')->insertGetId([
                    'workflow_definition_id'=>$definitionId,'version'=>1,'status'=>'PUBLISHED','change_summary'=>'Default configurable four-stage workflow','published_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
                ]);
            } else {
                DB::table('workflow_versions')->where('id',$versionId)->update(['status'=>'PUBLISHED','updated_at'=>now()]);
                DB::table('workflow_transitions')->where('workflow_version_id',$versionId)->delete();
                DB::table('workflow_stage_duties')->whereIn('workflow_stage_id', DB::table('workflow_stages')->where('workflow_version_id',$versionId)->pluck('id'))->delete();
                DB::table('workflow_stages')->where('workflow_version_id',$versionId)->delete();
            }

            $stageIds = [];
            foreach (self::STAGES as $index => $stage) {
                $roleId = Role::where('slug',$stage['role'])->value('id');
                $staffId = $stage['staff'] ? User::where('email',$stage['staff'])->value('id') : null;
                $stageIds[$stage['code']] = DB::table('workflow_stages')->insertGetId([
                    'workflow_version_id'=>$versionId,'name'=>$stage['name'],'code'=>$stage['code'],'stage_order'=>$index+1,
                    'required_permission'=>null,'responsible_role_id'=>$roleId,'assigned_user_id'=>$staffId,'assignment_mode'=>$stage['mode'],
                    'is_terminal'=>$stage['final'],'is_final'=>$stage['final'],'requires_placement'=>$stage['placement'],'is_starting'=>$index===0,
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
                foreach ($stage['duties'] as $dutyIndex => $dutyName) {
                    DB::table('workflow_stage_duties')->insert([
                        'workflow_stage_id'=>$stageIds[$stage['code']],
                        'name'=>$dutyName,
                        'code'=>strtoupper(trim(preg_replace('/[^A-Z0-9]+/i','_', $dutyName), '_')),
                        'duty_order'=>$dutyIndex+1,
                        'is_required'=>true,
                        'created_at'=>now(),'updated_at'=>now(),
                    ]);
                }
            }

            foreach (self::TRANSITIONS as $transition) {
                $permission = match ($transition['action']) {
                    'FORWARD' => 'applications.forward',
                    'RETURN' => 'applications.return',
                    'REJECT' => 'applications.reject',
                    'ACCEPT' => 'applications.accept',
                    'COMPLETE_PLACEMENT' => 'placements.manage',
                };
                DB::table('workflow_transitions')->insert([
                    'workflow_version_id'=>$versionId,'from_stage_id'=>$stageIds[$transition['from']],'to_stage_id'=>$stageIds[$transition['to']],
                    'action'=>$transition['action'],'label'=>$transition['label'],'result_status'=>$transition['status'],
                    'required_permission'=>$permission,'responsible_role_id'=>Role::where('slug',$transition['role'])->value('id'),
                    'requires_comment'=>$transition['comment'],'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        }
    }
}
