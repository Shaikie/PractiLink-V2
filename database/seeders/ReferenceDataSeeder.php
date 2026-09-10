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
                $unique = ['code' => $row['code']];
                DB::table($table)->updateOrInsert($unique, array_merge($row, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
