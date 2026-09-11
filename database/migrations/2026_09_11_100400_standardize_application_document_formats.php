<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('document_types')->update([
            'allowed_extensions' => json_encode(['pdf', 'jpg', 'jpeg', 'png', 'docx']),
            'allowed_mime_types' => json_encode([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]),
            'max_size_kb' => 10240,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Original limits differed between document types.
    }
};