<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@practilink.co.tz'],
            [
                'fullname' => 'PractiLink Administrator',
                'username' => 'admin',
                'phone' => null,
                'password' => 'Admin@12345',
                'is_active' => true,
            ]
        );

        $administratorRole = Role::where('slug', 'administrator')->firstOrFail();
        $admin->roles()->syncWithoutDetaching([$administratorRole->id]);
    }
}
