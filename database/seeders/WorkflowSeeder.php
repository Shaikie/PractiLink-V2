<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['INDUSTRIAL','INTERNSHIP'] as $trainingCode) {
            $trainingTypeId=DB::table('training_types')->where('code',$trainingCode)->value('id');
            $definitionId=DB::table('workflow_definitions')->where('code','DEFAULT_'.$trainingCode)->value('id');
            if(!$definitionId) $definitionId=DB::table('workflow_definitions')->insertGetId(['name'=>$trainingCode==='INDUSTRIAL'?'Industrial Training Approval':'Internship Approval','code'=>'DEFAULT_'.$trainingCode,'training_type_id'=>$trainingTypeId,'description'=>'Default configurable application approval workflow.','is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
            if(DB::table('workflow_versions')->where('workflow_definition_id',$definitionId)->exists()) continue;
            $versionId=DB::table('workflow_versions')->insertGetId(['workflow_definition_id'=>$definitionId,'version'=>1,'status'=>'PUBLISHED','change_summary'=>'Initial organizational workflow','published_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
            $stages=[['name'=>'Secretary Review','code'=>'SECRETARY_REVIEW','required_permission'=>'workflow.secretary_review','is_terminal'=>false],['name'=>'HOD Review','code'=>'HOD_REVIEW','required_permission'=>'workflow.hod_review','is_terminal'=>false],['name'=>'HR Review','code'=>'HR_REVIEW','required_permission'=>'workflow.hr_review','is_terminal'=>false],['name'=>'Accepted / Placement','code'=>'ACCEPTED_PLACEMENT','required_permission'=>'workflow.placement','is_terminal'=>true]];
            $ids=[]; foreach($stages as $i=>$stage) $ids[] = DB::table('workflow_stages')->insertGetId(array_merge($stage,['workflow_version_id'=>$versionId,'stage_order'=>$i+1,'created_at'=>now(),'updated_at'=>now()]));
            $transitions=[[$ids[0],$ids[1],'FORWARD','Forward','applications.forward',false],[$ids[1],$ids[2],'FORWARD','Forward','applications.forward',false],[$ids[2],$ids[3],'FORWARD','Accept / forward','applications.accept',false],[$ids[1],$ids[0],'RETURN','Return for correction','applications.return',true],[$ids[2],$ids[1],'RETURN','Return for correction','applications.return',true]];
            foreach($transitions as [$from,$to,$action,$label,$permission,$comment]) DB::table('workflow_transitions')->insert(['workflow_version_id'=>$versionId,'from_stage_id'=>$from,'to_stage_id'=>$to,'action'=>$action,'label'=>$label,'required_permission'=>$permission,'requires_comment'=>$comment,'created_at'=>now(),'updated_at'=>now()]);
        }
    }
}
