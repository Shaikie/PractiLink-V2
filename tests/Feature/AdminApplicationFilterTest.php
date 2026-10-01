<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use App\Models\WorkflowStage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApplicationFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_stage_filter_returns_only_applications_in_that_stage(): void
    {
        $this->seed(DatabaseSeeder::class);
        $application = Application::where('reference_number', 'like', 'DEMO-PT-%')->firstOrFail();
        $stage = WorkflowStage::where('code', $application->workflow->currentStage->code)->firstOrFail();

        $this->actingAs($this->administrator())
            ->get(route('admin.applications.index', ['stage' => $stage->code]))
            ->assertOk()
            ->assertSee($application->reference_number)
            ->assertSee('Reset');
    }

    public function test_stage_filter_with_no_matches_renders_an_empty_state(): void
    {
        $this->seed(DatabaseSeeder::class);
        $applicationStageIds = Application::query()
            ->with('workflow')
            ->get()
            ->map(fn (Application $application): ?int => $application->workflow?->current_stage_id)
            ->filter()
            ->all();
        $stageWithoutApplications = WorkflowStage::query()
            ->whereNotIn('id', $applicationStageIds)
            ->firstOrFail();

        $this->actingAs($this->administrator())
            ->get(route('admin.applications.index', ['stage' => $stageWithoutApplications->code]))
            ->assertOk()
            ->assertSee('No applications found');
    }

    public function test_invalid_stage_filter_is_rejected_and_redirected_with_validation_errors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->administrator())
            ->from(route('admin.applications.index'))
            ->get(route('admin.applications.index', ['stage' => 99999]))
            ->assertRedirect(route('admin.applications.index'))
            ->assertSessionHasErrors('stage');
    }

    private function administrator(): User
    {
        return User::where('email', 'admin@practilink.co.tz')->firstOrFail();
    }
}
