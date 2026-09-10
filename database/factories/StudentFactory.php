<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'registration_number' => 'REG-'.fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'nationality_id' => DB::table('nationalities')->inRandomOrder()->value('id'),
            'institution_id' => DB::table('institutions')->inRandomOrder()->value('id'),
            'course_id' => DB::table('courses')->inRandomOrder()->value('id'),
            'study_level_id' => DB::table('study_levels')->inRandomOrder()->value('id'),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }
}
