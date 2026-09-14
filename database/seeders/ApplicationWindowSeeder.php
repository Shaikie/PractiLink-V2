<?php

namespace Database\Seeders;

use App\Models\ApplicationWindow;
use App\Models\TrainingType;
use Illuminate\Database\Seeder;

class ApplicationWindowSeeder extends Seeder
{
    public function run(): void
    {
        $trainingType = TrainingType::where('code', 'INDUSTRIAL')->firstOrFail();

        ApplicationWindow::firstOrCreate(
            ['name' => 'Practical Training'],
            [
                'training_type_id' => $trainingType->id,
                'opens_at' => now()->subDay(),
                'closes_at' => now()->addMonths(6),
                'is_active' => true,
            ],
        );
    }
}
