<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario Admin (sin roles por ahora)
        User::create([
            'name' => 'Administrador',
            'email' => 'tecnologia@colbeef.com',
            'password' => Hash::make('SIRT123'),
            'email_verified_at' => now(),
        ]);

        echo "✅ Usuario Admin creado exitosamente\n";
        echo "📧 Email: tecnologia@colbeef.com\n";
        echo "🔑 Password: SIRT123\n";
    }
}
