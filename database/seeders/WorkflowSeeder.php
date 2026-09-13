<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $workflows = [
            'INDUSTRIAL' => ['name' => 'Industrial Training Application', 'code' => 'DEFAULT_INDUSTRIAL'],
            'INTERNSHIP' => ['name' => 'Internship Application', 'code' => 'DEFAULT_INTERNSHIP'],
        ];

        foreach ($workflows as $trainingCode => $definitionData) {
            $trainingTypeId = DB::table('training_types')->where('code', $trainingCode)->value('id');
            if (!$trainingTypeId) continue;

            $definitionId = DB::table('workflow_definitions')->where('code', $definitionData['code'])->value('id');
            if (!$definitionId) {
                $definitionId = DB::table('workflow_definitions')->insertGetId([
                    'name' => $definitionData['name'],
                    'code' => $definitionData['code'],
                    'training_type_id' => $trainingTypeId,
                    'description' => 'Standard student practical-training application workflow.',
                    'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            if (DB::table('workflow_versions')->where('workflow_definition_id', $definitionId)->exists()) continue;

            $versionId = DB::table('workflow_versions')->insertGetId([
                'workflow_definition_id' => $definitionId,
                'version' => 1,
                'status' => 'PUBLISHED',
                'change_summary' => 'Initial professional workflow',
                'published_at' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            $stages = [
                ['name'=>'Submitted','code'=>'SUBMITTED','role'=>'secretary','terminal'=>false,'starting'=>true],
                ['name'=>'Initial Review','code'=>'INITIAL_REVIEW','role'=>'secretary','terminal'=>false,'starting'=>false],
                ['name'=>'Department Review','code'=>'DEPARTMENT_REVIEW','role'=>'hod','terminal'=>false,'starting'=>false],
                ['name'=>'Final Review','code'=>'FINAL_REVIEW','role'=>'hr','terminal'=>false,'starting'=>false],
                ['name'=>'Placement','code'=>'PLACEMENT','role'=>'placement-officer','terminal'=>false,'starting'=>false],
                ['name'=>'Completed','code'=>'COMPLETED','role'=>null,'terminal'=>true,'starting'=>false],
            ];

            $stageIds = [];
            foreach ($stages as $index => $stage) {
                $stageIds[$stage['code']] = DB::table('workflow_stages')->insertGetId([
                    'workflow_version_id'=>$versionId,
                    'name'=>$stage['name'],
                    'code'=>$stage['code'],
                    'stage_order'=>$index+1,
                    'required_permission'=>null,
                    'responsible_role_id'=>$stage['role'] ? DB::table('roles')->where('slug',$stage['role'])->value('id') : null,
                    'is_terminal'=>$stage['terminal'],
                    'is_starting'=>$stage['starting'],
                    'created_at'=>now(), 'updated_at'=>now(),
                ]);
            }

            $transitions = [
                ['from'=>'SUBMITTED','to'=>'INITIAL_REVIEW','action'=>'FORWARD','label'=>'Start review','status'=>'UNDER_REVIEW','role'=>'secretary','comment'=>false],
                ['from'=>'INITIAL_REVIEW','to'=>'DEPARTMENT_REVIEW','action'=>'FORWARD','label'=>'Forward to department','status'=>'UNDER_REVIEW','role'=>'secretary','comment'=>false],
                ['from'=>'INITIAL_REVIEW','to'=>'SUBMITTED','action'=>'RETURN','label'=>'Return for correction','status'=>'RETURNED','role'=>'secretary','comment'=>true],
                ['from'=>'DEPARTMENT_REVIEW','to'=>'FINAL_REVIEW','action'=>'FORWARD','label'=>'Forward to final review','status'=>'UNDER_REVIEW','role'=>'hod','comment'=>false],
                ['from'=>'DEPARTMENT_REVIEW','to'=>'INITIAL_REVIEW','action'=>'RETURN','label'=>'Return to initial review','status'=>'RETURNED','role'=>'hod','comment'=>true],
                ['from'=>'DEPARTMENT_REVIEW','to'=>'COMPLETED','action'=>'REJECT','label'=>'Reject application','status'=>'REJECTED','role'=>'hod','comment'=>true],
                ['from'=>'FINAL_REVIEW','to'=>'PLACEMENT','action'=>'ACCEPT','label'=>'Approve for placement','status'=>'ACCEPTED','role'=>'hr','comment'=>false],
                ['from'=>'FINAL_REVIEW','to'=>'DEPARTMENT_REVIEW','action'=>'RETURN','label'=>'Return to department','status'=>'RETURNED','role'=>'hr','comment'=>true],
                ['from'=>'FINAL_REVIEW','to'=>'COMPLETED','action'=>'REJECT','label'=>'Reject application','status'=>'REJECTED','role'=>'hr','comment'=>true],
                ['from'=>'PLACEMENT','to'=>'COMPLETED','action'=>'COMPLETE_PLACEMENT','label'=>'Complete placement','status'=>'ACCEPTED','role'=>'placement-officer','comment'=>false],
            ];

            foreach ($transitions as $transition) {
                $permission = match ($transition['action']) {
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
                    'created_at'=>now(), 'updated_at'=>now(),
                ]);
            }
        }
    }
}
