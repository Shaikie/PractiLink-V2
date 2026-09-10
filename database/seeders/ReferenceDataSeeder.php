<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            'departments' => [
                ['name' => 'Human Resources', 'code' => 'HR'],
                ['name' => 'Information Technology', 'code' => 'IT'],
                ['name' => 'Operations', 'code' => 'OPS'],
            ],
            'institutions' => [
                ['name' => 'University of Dar es Salaam', 'code' => 'UDSM'],
                ['name' => 'Ardhi University', 'code' => 'ARU'],
                ['name' => 'Nelson Mandela African Institution of Science and Technology', 'code' => 'NM-AIST'],
            ],
            'courses' => [
                ['name' => 'Bachelor of Science in Computer Science', 'code' => 'BSC-CS'],
                ['name' => 'Bachelor of Science in Information Technology', 'code' => 'BSC-IT'],
                ['name' => 'Bachelor of Science in Computer Engineering', 'code' => 'BSC-CE'],
            ],
            'nationalities' => [
                ['name' => 'Tanzanian', 'code' => 'TZA'],
                ['name' => 'Kenyan', 'code' => 'KEN'],
                ['name' => 'Ugandan', 'code' => 'UGA'],
            ],
            'study_levels' => [
                ['name' => 'Certificate', 'code' => 'CERT'],
                ['name' => 'Diploma', 'code' => 'DIP'],
                ['name' => 'Bachelor Degree', 'code' => 'DEG'],
            ],
            'training_types' => [
                ['name' => 'Industrial Training', 'code' => 'INDUSTRIAL'],
                ['name' => 'Internship', 'code' => 'INTERNSHIP'],
            ],
            'specializations' => [
                ['name' => 'Software Development', 'code' => 'SOFTWARE'],
                ['name' => 'Network Administration', 'code' => 'NETWORK'],
                ['name' => 'Human Resource Management', 'code' => 'HRM'],
            ],
            'document_types' => [
                ['name' => 'Introduction Letter', 'code' => 'INTRODUCTION', 'is_required' => true],
                ['name' => 'Academic Transcript', 'code' => 'TRANSCRIPT', 'is_required' => true],
                ['name' => 'Placement Letter', 'code' => 'PLACEMENT_LETTER', 'is_required' => false],
            ],
            'training_report_statuses' => [
                ['name' => 'Draft', 'code' => 'DRAFT'],
                ['name' => 'Submitted', 'code' => 'SUBMITTED'],
                ['name' => 'Reviewed', 'code' => 'REVIEWED'],
            ],
            'training_completion_statuses' => [
                ['name' => 'In Progress', 'code' => 'IN_PROGRESS'],
                ['name' => 'Completed', 'code' => 'COMPLETED'],
                ['name' => 'Incomplete', 'code' => 'INCOMPLETE'],
            ],
        ];

        foreach ($tables as $table => $rows) {
            foreach ($rows as $row) {
                DB::table($table)->updateOrInsert(
                    ['code' => $row['code']],
                    array_merge($row, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }
    }
}
