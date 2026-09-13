<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    private const STAGES = [
        ['name'=>'Submitted','code'=>'SUBMITTED','role'=>null,'terminal'=>false,'starting'=>true],
        ['name'=>'Secretary Review','code'=>'SECRETARY_REVIEW','role'=>'secretary','terminal'=>false,'starting'=>false],
        ['name'=>'HR Review','code'=>'HR_REVIEW','role'=>'hr','terminal'=>false,'starting'=>false],
        ['name'=>'Department Review','code'=>'DEPARTMENT_REVIEW','role'=>'hod','terminal'=>false,'starting'=>false],
        ['name'=>'CTO Placement','code'=>'CTO_PLACEMENT','role'=>'cto','terminal'=>false,'starting'=>false],
        ['name'=>'Completed','code'=>'COMPLETED','role'=>null,'terminal'=>true,'starting'=>false],
    ];

    private const TRANSITIONS = [
        ['from'=>'SUBMITTED','to'=>'SECRETARY_REVIEW','action'=>'START_REVIEW','label'=>'Start review','status'=>'UNDER_REVIEW','role'=>'secretary','comment'=>false],
        ['from'=>'SECRETARY_REVIEW','to'=>'HR_REVIEW','action'=>'FORWARD','label'=>'Send to HR review','status'=>'UNDER_REVIEW','role'=>'secretary','comment'=>false],
        ['from'=>'SECRETARY_REVIEW','to'=>'SUBMITTED','action'=>'RETURN','label'=>'Return to student','status'=>'RETURNED','role'=>'secretary','comment'=>true],
        ['from'=>'SECRETARY_REVIEW','to'=>'COMPLETED','action'=>'REJECT','label'=>'Reject application','status'=>'REJECTED','role'=>'secretary','comment'=>true],
        ['from'=>'HR_REVIEW','to'=>'DEPARTMENT_REVIEW','action'=>'FORWARD','label'=>'Send to department HOD','status'=>'UNDER_REVIEW','role'=>'hr','comment'=>false],
        ['from'=>'HR_REVIEW','to'=>'SECRETARY_REVIEW','action'=>'RETURN','label'=>'Return to secretary','status'=>'RETURNED','role'=>'hr','comment'=>true],
        ['from'=>'HR_REVIEW','to'=>'COMPLETED','action'=>'REJECT','label'=>'Reject application','status'=>'REJECTED','role'=>'hr','comment'=>true],
        ['from'=>'DEPARTMENT_REVIEW','to'=>'CTO_PLACEMENT','action'=>'ACCEPT','label'=>'Approve for CTO placement','status'=>'ACCEPTED','role'=>'hod','comment'=>false],
        ['from'=>'DEPARTMENT_REVIEW','to'=>'HR_REVIEW','action'=>'RETURN','label'=>'Return to HR','status'=>'RETURNED','role'=>'hod','comment'=>true],
        ['from'=>'DEPARTMENT_REVIEW','to'=>'COMPLETED','action'=>'REJECT','label'=>'Reject application','status'=>'REJECTED','role'=>'hod','comment'=>true],
        ['from'=>'CTO_PLACEMENT','to'=>'COMPLETED','action'=>'COMPLETE_PLACEMENT','label'=>'Complete placement','status'=>'ACCEPTED','role'=>'cto','comment'=>false],
    ];

    public function run(): void
    {
        foreach (['INDUSTRIAL'=>'Industrial Training Application','INTERNSHIP'=>'Internship Application'] as $trainingCode => $name) {
            $trainingTypeId = DB::table('training_types')->where('code',$trainingCode)->value('id');
            if (!$trainingTypeId) continue;

            $definitionId = DB::table('workflow_definitions')->where('code','DEFAULT_'.$trainingCode)->value('id');
            if (!$definitionId) {
                $definitionId = DB::table('workflow_definitions')->insertGetId([
                    'name'=>$name,
                    'code'=>'DEFAULT_'.$trainingCode,
                    'training_type_id'=>$trainingTypeId,
                    'description'=>'Secretary → HR → Department HOD → CTO placement workflow.',
                    'is_active'=>true,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }

            $published = DB::table('workflow_versions')
                ->where('workflow_definition_id',$definitionId)
                ->where('status','PUBLISHED')
                ->latest('version')
                ->first();

            if ($published && $this->matchesPublishedWorkflow($published->id)) continue;

            $versionNumber = ((int) DB::table('workflow_versions')->where('workflow_definition_id',$definitionId)->max('version')) + 1;
            $versionId = DB::table('workflow_versions')->insertGetId([
                'workflow_definition_id'=>$definitionId,
                'version'=>$versionNumber,
                'status'=>'PUBLISHED',
                'change_summary'=>'Secretary → HR → Department HOD → CTO placement workflow',
                'published_at'=>now(),
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);

            $stageIds = [];
            foreach (self::STAGES as $index => $stage) {
                $roleId = $stage['role'] ? DB::table('roles')->where('slug',$stage['role'])->value('id') : null;
                if ($stage['role'] && !$roleId) continue;

                $stageIds[$stage['code']] = DB::table('workflow_stages')->insertGetId([
                    'workflow_version_id'=>$versionId,
                    'name'=>$stage['name'],
                    'code'=>$stage['code'],
                    'stage_order'=>$index+1,
                    'required_permission'=>null,
                    'responsible_role_id'=>$roleId,
                    'is_terminal'=>$stage['terminal'],
                    'is_starting'=>$stage['starting'],
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }

            foreach (self::TRANSITIONS as $transition) {
                $permission = match ($transition['action']) {
                    'START_REVIEW' => 'applications.review',
                    'FORWARD' => 'applications.forward',
                    'RETURN' => 'applications.return',
                    'REJECT' => 'applications.reject',
                    'ACCEPT' => 'applications.accept',
                    'COMPLETE_PLACEMENT' => 'placements.manage',
                };

                DB::table('workflow_transitions')->insert([
                    'workflow_version_id'=>$versionId,
                    'from_stage_id'=>$stageIds[$transition['from']],
                    'to_stage_id'=>$stageIds[$transition['to']],
                    'action'=>$transition['action'],
                    'label'=>$transition['label'],
                    'result_status'=>$transition['status'],
                    'required_permission'=>$permission,
                    'responsible_role_id'=>DB::table('roles')->where('slug',$transition['role'])->value('id'),
                    'requires_comment'=>$transition['comment'],
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }

            if ($published) {
                DB::table('workflow_versions')->where('id',$published->id)->update(['status'=>'ARCHIVED','updated_at'=>now()]);
            }
        }
    }

    private function matchesPublishedWorkflow(int $versionId): bool
    {
        $stageCodes = DB::table('workflow_stages')->where('workflow_version_id',$versionId)->orderBy('stage_order')->pluck('code')->all();
        return $stageCodes === array_column(self::STAGES,'code')
            && DB::table('workflow_transitions')->where('workflow_version_id',$versionId)->count() === count(self::TRANSITIONS)
            && DB::table('workflow_transitions')->where('workflow_version_id',$versionId)->where('action','START_REVIEW')->exists();
    }
}
