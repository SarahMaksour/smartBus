<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@smartbus.com'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'SmartBus',
                'password'   => Hash::make('12345678'),
                'is_active'  => true,
            ]
        );

        $admin->assignRole('admin');

        $this->command->info('تم إنشاء الأدمن: ' . $admin->email);
    }
}