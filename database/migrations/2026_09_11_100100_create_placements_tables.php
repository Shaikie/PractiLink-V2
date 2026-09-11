<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supervisor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference_number')->unique();
            $table->string('status')->default('PENDING');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('placement_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            $table->index(['placement_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_status_history');
        Schema::dropIfExists('placements');
        Schema::dropIfExists('organizations');
    }
};
