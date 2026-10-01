<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationWindow;
use App\Models\Department;
use App\Models\Student;
use Database\Seeders\ApplicationWindowSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApplicationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_application_create_form_renders_a_post_form_without_method_override(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $this->seed(ApplicationWindowSeeder::class);
        $student = Student::factory()->create();

        $this->actingAs($student, 'students')
            ->get(route('student.applications.create'))
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertDontSee('name="_method"', false);
    }

    public function test_student_can_save_a_draft_from_the_create_form(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $this->seed(ApplicationWindowSeeder::class);
        $student = Student::factory()->create();
        $window = ApplicationWindow::firstOrFail();
        $department = Department::firstOrFail();

        $this->actingAs($student, 'students')
            ->post(route('student.applications.store'), [
                'application_window_id' => $window->id,
                'department_id' => $department->id,
                'reason_for_application' => 'I want to gain practical experience in a professional engineering environment.',
                'interests' => 'Backend engineering and secure web application development.',
                'expected_objectives' => 'Learn how professional engineering teams deliver reliable software.',
                'current_study_year' => 3,
                'training_start_date' => now()->addWeek()->toDateString(),
                'training_end_date' => now()->addMonths(2)->toDateString(),
                'notes' => 'Please consider my availability during the academic break.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'student_id' => $student->id,
            'application_window_id' => $window->id,
            'status' => 'DRAFT',
        ]);
        $this->assertModelExists(Application::query()->where('student_id', $student->id)->firstOrFail());
    }
}
