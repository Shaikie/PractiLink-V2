<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->json('allowed_extensions')->nullable()->after('is_required');
            $table->json('allowed_mime_types')->nullable()->after('allowed_extensions');
            $table->unsignedInteger('max_size_kb')->default(5120)->after('allowed_mime_types');
            $table->unsignedInteger('min_size_kb')->default(1)->after('max_size_kb');
            $table->boolean('is_active')->default(true)->after('min_size_kb');
            $table->text('description')->nullable()->after('is_active');
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained()->restrictOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('disk', 50)->default('local');
            $table->string('path');
            $table->string('extension', 20);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->unique(['application_id', 'document_type_id']);
            $table->index(['application_id', 'created_at']);
            $table->index('sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn([
                'allowed_extensions', 'allowed_mime_types', 'max_size_kb',
                'min_size_kb', 'is_active', 'description',
            ]);
        });
    }
};
