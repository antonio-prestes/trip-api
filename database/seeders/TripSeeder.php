<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TripRequest;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        // Seleciona todos os usuários normais (não admin)
        $users = User::where('role', '!=', 'admin')->get();
        foreach ($users as $user) {
            TripRequest::factory(5)->create([
                'user_id' => $user->id,
            ]);
        }
    }
}

