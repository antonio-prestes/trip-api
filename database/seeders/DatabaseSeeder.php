<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cria usuário admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Cria 5 usuários normais
        $this->call([
            UserSeeder::class,
        ]);

        // Cria status de viagem
        $this->call([
            TripStatusSeeder::class,
        ]);

        // Cria 5 trips para cada usuário normal
        $this->call([
            TripSeeder::class,
        ]);
    }
}
