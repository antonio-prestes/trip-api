<?php

namespace Database\Seeders;

use App\Models\TripStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TripStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'solicitado',
                'description' => 'Solicitação de viagem criada e aguardando análise',
                'color' => '#F59E0B', // Amarelo/laranja
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'aprovado',
                'description' => 'Solicitação de viagem aprovada',
                'color' => '#10B981', // Verde
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'cancelado',
                'description' => 'Solicitação de viagem cancelada',
                'color' => '#EF4444', // Vermelho
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($statuses as $status) {
            TripStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }

        $this->command->info('Status de viagem criados com sucesso!');
    }
}
