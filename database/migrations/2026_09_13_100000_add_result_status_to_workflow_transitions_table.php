<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->string('result_status', 30)->nullable()->after('label');
            $table->index('result_status');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->dropIndex(['result_status']);
            $table->dropColumn('result_status');
        });
    }
};
