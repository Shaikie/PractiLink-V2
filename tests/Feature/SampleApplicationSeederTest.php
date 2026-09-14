<?php

namespace Tests\Feature;

use App\Models\Application;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleApplicationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeding_creates_a_submitted_practical_training_application_at_the_workflow_start(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $application = Application::with(['applicationWindow.trainingType', 'workflow.currentStage'])
            ->where('reference_number', 'like', 'DEMO-PT-%')
            ->firstOrFail();

        $this->assertSame('SUBMITTED', $application->status);
        $this->assertSame('Practical Training', $application->applicationWindow->name);
        $this->assertSame('INDUSTRIAL', $application->applicationWindow->trainingType->code);
        $this->assertSame('SUBMITTED', $application->workflow->currentStage->code);
        $this->assertNotNull($application->submitted_at);
        $this->assertNotEmpty($application->reason_for_application);
        $this->assertNotEmpty($application->interests);
        $this->assertNotEmpty($application->expected_objectives);
    }
}
