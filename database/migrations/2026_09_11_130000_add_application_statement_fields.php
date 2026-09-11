<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('reason_for_application')->nullable()->after('notes');
            $table->text('interests')->nullable()->after('reason_for_application');
            $table->text('expected_objectives')->nullable()->after('interests');
            $table->unsignedTinyInteger('current_study_year')->nullable()->after('expected_objectives');
            $table->index(['current_study_year', 'status'], 'applications_year_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('applications_year_status_idx');
            $table->dropColumn(['reason_for_application', 'interests', 'expected_objectives', 'current_study_year']);
        });
    }
};
