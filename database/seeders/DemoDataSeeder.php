<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'Demo@12345';

        if (! app()->environment(['local', 'testing'])) {
            throw new \LogicException(
                'Demo data can only be seeded locally or in testing environments.',
            );
        }

        Organization::updateOrCreate(
            ['code' => 'PRACTILINK'],
            [
                'name' => 'PractiLink',
                'email' => 'info@practilink.co.tz',
                'phone' => null,
                'address' => null,
                'is_active' => true,
            ],
        );

        $staff = [
            ['fullname' => 'Demo Secretary', 'username' => 'demo.secretary', 'email' => 'secretary.demo@practilink.test', 'role' => 'secretary'],
            ['fullname' => 'Demo Head of Department', 'username' => 'demo.hod', 'email' => 'hod.demo@practilink.test', 'role' => 'hod'],
            ['fullname' => 'Demo HR Officer', 'username' => 'demo.hr', 'email' => 'hr.demo@practilink.test', 'role' => 'hr'],
            ['fullname' => 'Demo Placement Officer', 'username' => 'demo.placement', 'email' => 'placement.demo@practilink.test', 'role' => 'placement-officer'],
            ['fullname' => 'Demo Supervisor', 'username' => 'demo.supervisor', 'email' => 'supervisor.demo@practilink.test', 'role' => 'supervisor'],
            ['fullname' => 'Demo Chief Technology Officer', 'username' => 'demo.cto', 'email' => 'cto.demo@practilink.test', 'role' => 'cto'],
        ];

        foreach ($staff as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'fullname' => $data['fullname'],
                    'username' => $data['username'],
                    'phone' => null,
                    'password' => $password,
                    'is_active' => true,
                ],
            );

            $role = Role::where('slug', $data['role'])->firstOrFail();
            $user->roles()->sync([$role->id]);

            if ($data['role'] === 'hod') {
                $department = Department::where('code', 'IT')->firstOrFail();
                $department->users()->syncWithoutDetaching([$user->id]);
            }
        }

        $referenceId = static function (string $table, string $code): int {
            $id = DB::table($table)->where('code', $code)->value('id');

            if (! $id) {
                throw new \RuntimeException("Missing {$table} reference data for code [{$code}].");
            }

            return $id;
        };

        $students = [
            [
                'first_name' => 'Amina',
                'last_name' => 'Mashauri',
                'registration_number' => 'DEMO-2026-001',
                'email' => 'amina.mashauri@practilink.test',
                'phone' => '+255700000001',
                'gender' => 'female',
                'nationality_code' => 'TZA',
                'institution_code' => 'UDSM',
                'course_code' => 'BSC-CS',
                'study_level_code' => 'DEG',
            ],
            [
                'first_name' => 'Daniel',
                'last_name' => 'Otieno',
                'registration_number' => 'DEMO-2026-002',
                'email' => 'daniel.otieno@practilink.test',
                'phone' => '+255700000002',
                'gender' => 'male',
                'nationality_code' => 'KEN',
                'institution_code' => 'ARU',
                'course_code' => 'BSC-IT',
                'study_level_code' => 'DEG',
            ],
            [
                'first_name' => 'Neema',
                'last_name' => 'Kato',
                'registration_number' => 'DEMO-2026-003',
                'email' => 'neema.kato@practilink.test',
                'phone' => '+255700000003',
                'gender' => 'female',
                'nationality_code' => 'UGA',
                'institution_code' => 'NM-AIST',
                'course_code' => 'BSC-CE',
                'study_level_code' => 'DIP',
            ],
        ];

        foreach ($students as $data) {
            Student::updateOrCreate(
                ['registration_number' => $data['registration_number']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'gender' => $data['gender'],
                    'nationality_id' => $referenceId('nationalities', $data['nationality_code']),
                    'institution_id' => $referenceId('institutions', $data['institution_code']),
                    'course_id' => $referenceId('courses', $data['course_code']),
                    'study_level_id' => $referenceId('study_levels', $data['study_level_code']),
                    'password' => $password,
                    'remember_token' => Str::random(10),
                    'is_active' => true,
                ],
            );
        }

        $this->call(SampleApplicationSeeder::class);
    }
}
