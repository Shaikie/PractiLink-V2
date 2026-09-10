<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_window_id')->constrained()->restrictOnDelete();
            $table->string('reference_number', 40)->unique();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED', 'CANCELLED'])->default('DRAFT');
            $table->text('notes')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'application_window_id']);
            $table->index(['student_id', 'status']);
            $table->index(['application_window_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
