<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->date('training_start_date')->nullable()->after('notes');
            $table->date('training_end_date')->nullable()->after('training_start_date');
            $table->index(['training_start_date', 'training_end_date']);
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['training_start_date', 'training_end_date']);
            $table->dropColumn(['training_start_date', 'training_end_date']);
        });
    }
};
