<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('password')->after('email');
            $table->rememberToken()->after('password');
            $table->dateTime('last_login_at')->nullable()->after('remember_token');
            $table->dateTime('locked_at')->nullable()->after('last_login_at');
            $table->boolean('is_active')->default(true)->after('locked_at');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'remember_token',
                'last_login_at',
                'locked_at',
                'is_active',
            ]);
        });
    }
};
