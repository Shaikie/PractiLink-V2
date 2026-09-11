<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['INDUSTRIAL', 'INTERNSHIP'] as $trainingCode) {
            $trainingTypeId = DB::table('training_types')->where('code', $trainingCode)->value('id');
            $definitionId = DB::table('workflow_definitions')->where('code', 'DEFAULT_'.$trainingCode)->value('id');
            if (! $definitionId) {
                $definitionId = DB::table('workflow_definitions')->insertGetId([
                    'name' => $trainingCode === 'INDUSTRIAL' ? 'Industrial Training Approval' : 'Internship Approval',
                    'code' => 'DEFAULT_'.$trainingCode,
                    'training_type_id' => $trainingTypeId,
                    'description' => 'Default versioned application approval workflow.',
                    'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            if (! DB::table('workflow_versions')->where('workflow_definition_id', $definitionId)->exists()) {
                $versionId = DB::table('workflow_versions')->insertGetId([
                    'workflow_definition_id' => $definitionId, 'version' => 1, 'status' => 'PUBLISHED',
                    'change_summary' => 'Initial default workflow', 'published_at' => now(),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $stages = [
                    ['name'=>'Application Review','code'=>'APPLICATION_REVIEW','required_permission'=>'applications.review','is_terminal'=>false],
                    ['name'=>'Placement Approval','code'=>'PLACEMENT_APPROVAL','required_permission'=>'applications.review','is_terminal'=>false],
                    ['name'=>'Ready for Placement','code'=>'READY_FOR_PLACEMENT','required_permission'=>'placements.manage','is_terminal'=>true],
                ];
                $ids=[];
                foreach ($stages as $i=>$stage) $ids[] = DB::table('workflow_stages')->insertGetId(array_merge($stage,['workflow_version_id'=>$versionId,'stage_order'=>$i+1,'created_at'=>now(),'updated_at'=>now()]));
                DB::table('workflow_transitions')->insert([
                    ['workflow_version_id'=>$versionId,'from_stage_id'=>$ids[0],'to_stage_id'=>$ids[1],'action'=>'FORWARD','label'=>'Forward','required_permission'=>'applications.review','requires_comment'=>false,'created_at'=>now(),'updated_at'=>now()],
                    ['workflow_version_id'=>$versionId,'from_stage_id'=>$ids[1],'to_stage_id'=>$ids[2],'action'=>'FORWARD','label'=>'Forward to placement','required_permission'=>'applications.review','requires_comment'=>false,'created_at'=>now(),'updated_at'=>now()],
                    ['workflow_version_id'=>$versionId,'from_stage_id'=>$ids[1],'to_stage_id'=>$ids[0],'action'=>'RETURN','label'=>'Return','required_permission'=>'applications.review','requires_comment'=>true,'created_at'=>now(),'updated_at'=>now()],
                ]);
            }
        }
    }
}
