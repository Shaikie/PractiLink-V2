<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('training_type_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('workflow_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('status')->default('DRAFT');
            $table->text('change_summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['workflow_definition_id', 'version']);
            $table->index(['workflow_definition_id', 'status']);
        });

        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_version_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->unsignedInteger('stage_order');
            $table->string('required_permission')->nullable();
            $table->boolean('is_terminal')->default(false);
            $table->timestamps();
            $table->unique(['workflow_version_id', 'code']);
            $table->unique(['workflow_version_id', 'stage_order']);
        });

        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_stage_id')->constrained('workflow_stages')->cascadeOnDelete();
            $table->foreignId('to_stage_id')->constrained('workflow_stages')->cascadeOnDelete();
            $table->string('action');
            $table->string('label');
            $table->string('required_permission')->nullable();
            $table->boolean('requires_comment')->default(false);
            $table->timestamps();
            $table->unique(['workflow_version_id', 'from_stage_id', 'to_stage_id', 'action']);
        });

        Schema::create('application_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('workflow_stages')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('application_workflow_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_workflow_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained('workflow_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->nullable()->constrained('workflow_stages')->nullOnDelete();
            $table->foreignId('transition_id')->nullable()->constrained('workflow_transitions')->nullOnDelete();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();
            $table->index(['application_workflow_id', 'acted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_workflow_history');
        Schema::dropIfExists('application_workflows');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflow_versions');
        Schema::dropIfExists('workflow_definitions');
    }
};
