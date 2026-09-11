<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->foreignId('responsible_role_id')->nullable()->after('required_permission')->constrained('roles')->nullOnDelete();
            $table->boolean('is_starting')->default(false)->after('is_terminal');
            $table->index(['workflow_version_id', 'is_starting'], 'workflow_stage_starting_idx');
        });

        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->foreignId('responsible_role_id')->nullable()->after('required_permission')->constrained('roles')->nullOnDelete();
            $table->index(['workflow_version_id', 'responsible_role_id'], 'workflow_transition_role_idx');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->dropIndex('workflow_transition_role_idx');
            $table->dropConstrainedForeignId('responsible_role_id');
        });

        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropIndex('workflow_stage_starting_idx');
            $table->dropConstrainedForeignId('responsible_role_id');
            $table->dropColumn('is_starting');
        });
    }
};
