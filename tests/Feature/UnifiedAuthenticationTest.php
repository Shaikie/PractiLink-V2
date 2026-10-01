<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UnifiedAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_root_redirects_guests_to_login(): void
    {
        $this->get(route('home'))->assertRedirect(route('login'));
    }

    public function test_user_can_login_with_email_from_shared_login_page(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.test', 'password' => Hash::make('password123'), 'is_active' => true]);
        $response = $this->post(route('login'), ['login' => $user->email, 'password' => 'password123']);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertGuest('students');
    }

    public function test_user_can_login_with_username_from_shared_login_page(): void
    {
        $user = User::factory()->create(['username' => 'staff-login', 'password' => Hash::make('password123'), 'is_active' => true]);
        $response = $this->post(route('login'), ['login' => 'staff-login', 'password' => 'password123']);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_student_can_login_with_email_from_shared_login_page(): void
    {
        $student = Student::factory()->create(['email' => 'student@example.test', 'password' => Hash::make('password123'), 'is_active' => true]);
        $response = $this->post(route('login'), ['login' => $student->email, 'password' => 'password123']);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($student, 'students');
        $this->assertGuest('web');
    }

    public function test_student_can_login_with_registration_number(): void
    {
        $student = Student::factory()->create(['registration_number' => 'REG-TEST-001', 'password' => Hash::make('password123')]);
        $response = $this->post(route('login'), ['login' => 'REG-TEST-001', 'password' => 'password123']);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($student, 'students');
    }

    public function test_student_registration_does_not_create_a_user(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => 'Test', 'last_name' => 'Student', 'registration_number' => 'REG-TEST-001',
            'email' => 'new.student@example.test', 'phone' => '0712345678', 'gender' => 'other',
            'nationality_id' => DB::table('nationalities')->where('code', 'TZA')->value('id'),
            'institution_id' => DB::table('institutions')->where('code', 'UDSM')->value('id'),
            'course_id' => DB::table('courses')->where('code', 'BSC-CS')->value('id'),
            'study_level_id' => DB::table('study_levels')->where('code', 'DEG')->value('id'),
            'password' => 'password123', 'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('students', ['email' => 'new.student@example.test']);
        $this->assertDatabaseMissing('users', ['email' => 'new.student@example.test']);
    }

    public function test_repeated_failed_login_attempts_are_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('login'), [
                'login' => 'missing@example.test',
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('login');
        }

        $this->post(route('login'), [
            'login' => 'missing@example.test',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    public function test_deactivated_student_session_is_rejected_on_the_next_request(): void
    {
        $student = Student::factory()->create(['is_active' => true]);
        $this->actingAs($student, 'students');
        $student->update(['is_active' => false]);

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');
        $this->assertGuest('students');
    }

    public function test_deactivated_staff_session_is_rejected_on_the_next_request(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user, 'web');
        $user->update(['is_active' => false]);

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');
        $this->assertGuest('web');
    }

    public function test_notification_read_action_requires_post_and_marks_only_the_owned_notification(): void
    {
        $student = Student::factory()->create();
        $notification = $student->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => ['title' => 'Status update', 'message' => 'Your application moved forward.'],
        ]);

        $this->actingAs($student, 'students')
            ->get(route('notifications.read', $notification->id))
            ->assertMethodNotAllowed();

        $this->actingAs($student, 'students')
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_inactive_student_cannot_login(): void
    {
        $student = Student::factory()->create(['email' => 'inactive@example.test', 'password' => Hash::make('password123'), 'is_active' => false]);
        $this->post(route('login'), ['login' => $student->email, 'password' => 'password123'])->assertSessionHasErrors('login');
        $this->assertGuest('students');
    }
}
