<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->after('responsible_role_id')->constrained('users')->nullOnDelete();
            $table->string('assignment_mode', 30)->default('ROLE')->after('assigned_user_id');
            $table->boolean('is_final')->default(false)->after('is_terminal');
            $table->boolean('requires_placement')->default(false)->after('is_final');
        });

        Schema::table('application_workflows', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->after('current_stage_id')->constrained('users')->nullOnDelete();
        });

        Schema::create('workflow_stage_duties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_stage_id')->constrained('workflow_stages')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 100);
            $table->text('description')->nullable();
            $table->unsignedInteger('duty_order')->default(1);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->unique(['workflow_stage_id', 'code']);
        });

        Schema::create('application_workflow_duties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_workflow_id')->constrained('application_workflows')->cascadeOnDelete();
            $table->foreignId('workflow_stage_id')->constrained('workflow_stages')->cascadeOnDelete();
            $table->foreignId('workflow_stage_duty_id')->constrained('workflow_stage_duties')->cascadeOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['application_workflow_id', 'workflow_stage_duty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_workflow_duties');
        Schema::dropIfExists('workflow_stage_duties');

        Schema::table('application_workflows', function (Blueprint $table) {
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn('assigned_user_id');
        });

        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn(['assigned_user_id', 'assignment_mode', 'is_final', 'requires_placement']);
        });
    }
};
