<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('ADMIN_PASSWORD', 'Admin@12345');

        $user = User::create([
            'name'               => 'Administrador',
            'first_name'         => 'Administrador',
            'last_name'          => 'Sistema',
            'username'           => 'admin',
            'email'              => env('ADMIN_EMAIL', 'tecnologia@colbeef.com'),
            'password'           => Hash::make($password),
            'email_verified_at'  => now(),
            'active'             => true,
        ]);

        $user->assignRole('admin');

        $this->command->info('Usuario Admin creado. Username: admin');
    }
}
