<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_assigned_role_can_complete_the_seeded_practical_training_workflow(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $application = Application::where('reference_number', 'like', 'DEMO-PT-%')->firstOrFail();

        $this->actAs('secretary.demo@practilink.test')
            ->post(route('admin.applications.action', $application), ['action' => 'START_REVIEW'])
            ->assertRedirect(route('admin.applications.index'));
        $this->assertWorkflowStage($application, 'SECRETARY_REVIEW', 'UNDER_REVIEW');

        $this->actAs('secretary.demo@practilink.test')
            ->post(route('admin.applications.action', $application), ['action' => 'FORWARD'])
            ->assertRedirect(route('admin.applications.index'));
        $this->assertWorkflowStage($application, 'HR_REVIEW', 'UNDER_REVIEW');

        $this->actAs('hr.demo@practilink.test')
            ->post(route('admin.applications.action', $application), ['action' => 'FORWARD'])
            ->assertRedirect(route('admin.applications.index'));
        $this->assertWorkflowStage($application, 'DEPARTMENT_REVIEW', 'UNDER_REVIEW');

        $this->actAs('hod.demo@practilink.test')
            ->post(route('admin.applications.action', $application), ['action' => 'ACCEPT'])
            ->assertRedirect(route('admin.applications.index'));
        $this->assertWorkflowStage($application, 'CTO_PLACEMENT', 'ACCEPTED');

        $organization = Organization::create([
            'name' => 'Sample Training Organization',
            'code' => 'SAMPLE-ORG',
            'is_active' => true,
        ]);
        $application->refresh();

        $this->actAs('cto.demo@practilink.test')
            ->post(route('admin.placements.store', $application), [
                'organization_id' => $organization->id,
                'department_id' => $application->department_id,
                'start_date' => $application->training_start_date->format('Y-m-d'),
                'end_date' => $application->training_end_date->format('Y-m-d'),
            ])
            ->assertRedirect(route('admin.placements.index'));

        $this->assertWorkflowStage($application, 'COMPLETED', 'ACCEPTED');
        $this->assertDatabaseHas('placements', ['application_id' => $application->id, 'status' => 'ALLOCATED']);
    }

    private function actAs(string $email): static
    {
        return $this->actingAs(User::where('email', $email)->firstOrFail(), 'web');
    }

    private function assertWorkflowStage(Application $application, string $stage, string $status): void
    {
        $application->refresh()->load('workflow.currentStage');

        $this->assertSame($status, $application->status);
        $this->assertSame($stage, $application->workflow->currentStage->code);
    }
}
