<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE applications MODIFY status ENUM(
                'DRAFT',
                'SUBMITTED',
                'UNDER_REVIEW',
                'RETURNED',
                'ACCEPTED',
                'PLACED',
                'REJECTED',
                'CANCELLED'
            ) NOT NULL DEFAULT 'DRAFT'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE applications MODIFY status ENUM(
                'DRAFT',
                'SUBMITTED',
                'UNDER_REVIEW',
                'RETURNED',
                'ACCEPTED',
                'REJECTED',
                'CANCELLED'
            ) NOT NULL DEFAULT 'DRAFT'"
        );
    }
};
