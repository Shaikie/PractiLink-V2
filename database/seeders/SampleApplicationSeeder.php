<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationWindow;
use App\Models\ApplicationWorkflow;
use App\Models\ApplicationWorkflowHistory;
use App\Models\Department;
use App\Models\Student;
use App\Models\WorkflowDefinition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleApplicationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $student = Student::where('email', 'amina.mashauri@practilink.test')->firstOrFail();
            $window = ApplicationWindow::where('name', 'Practical Training')->firstOrFail();
            $department = Department::where('code', 'IT')->firstOrFail();
            $definition = WorkflowDefinition::where('code', 'DEFAULT_INDUSTRIAL')->firstOrFail();
            $version = $definition->publishedVersion();

            if (! $version) {
                throw new \LogicException('The Industrial Training workflow must have a published version.');
            }

            $startingStage = $version->stages()
                ->where('is_starting', true)
                ->firstOrFail();

            $application = Application::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'application_window_id' => $window->id,
                ],
                [
                    'department_id' => $department->id,
                    'reference_number' => "DEMO-PT-{$student->id}-{$window->id}",
                    'status' => 'SUBMITTED',
                    'reason_for_application' => 'I am applying for practical training to apply my coursework in a professional software development environment.',
                    'interests' => 'Backend development, database design, secure web applications, and collaborative software delivery.',
                    'expected_objectives' => 'Strengthen practical engineering skills, learn professional delivery practices, and contribute to a supervised project.',
                    'current_study_year' => 3,
                    'training_start_date' => now()->addMonth()->startOfDay(),
                    'training_end_date' => now()->addMonths(3)->startOfDay(),
                    'notes' => 'Sample application seeded for workflow testing.',
                    'submitted_at' => now(),
                ],
            );

            $workflow = ApplicationWorkflow::firstOrCreate(
                ['application_id' => $application->id],
                [
                    'workflow_version_id' => $version->id,
                    'current_stage_id' => $startingStage->id,
                ],
            );

            ApplicationWorkflowHistory::firstOrCreate(
                [
                    'application_workflow_id' => $workflow->id,
                    'to_stage_id' => $startingStage->id,
                    'transition_id' => null,
                ],
                ['acted_at' => now()],
            );
        });
    }
}
