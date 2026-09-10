<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_windows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('training_type_id')->constrained()->restrictOnDelete();
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'opens_at', 'closes_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_windows');
    }
};
