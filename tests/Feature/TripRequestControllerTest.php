<?php

namespace Tests\Feature;

use App\Models\TripRequest;
use App\Models\TripStatus;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TripRequestControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $adminUser;
    protected $regularUser;
    protected $adminToken;
    protected $regularToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Desabilitar restrições de chave estrangeira para SQLite durante os testes
        if (env('DB_CONNECTION') === 'sqlite') {
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        }

        // Popular status de viagem
        $this->artisan('db:seed', ['--class' => 'TripStatusSeeder']);

        // Habilitar restrições de chave estrangeira após a população
        if (env('DB_CONNECTION') === 'sqlite') {
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        }

        // Criar usuários
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);

        // Gerar tokens
        $this->adminToken = JWTAuth::fromUser($this->adminUser);
        $this->regularToken = JWTAuth::fromUser($this->regularUser);
    }

    /**
     * Teste de usuário comum pode criar uma solicitação de viagem.
     */
    public function test_regular_user_can_create_trip_request(): void
    {
        $tripData = [
            'destination' => $this->faker->city,
            'departure_date' => now()->addDays(5)->format('Y-m-d'),
            'return_date' => now()->addDays(10)->format('Y-m-d'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->regularToken)
                         ->postJson('/api/trip-requests', $tripData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'destination',
                'departure_date',
                'return_date',
                'status_id',
                'created_at',
                'updated_at',
            ])
            ->assertJson(['user_id' => $this->regularUser->id]);

        $this->assertDatabaseHas('trip_requests', [
            'user_id' => $this->regularUser->id,
            'destination' => $tripData['destination'],
        ]);
    }

    /**
     * Teste de falha na criação de solicitação de viagem com erros de validação.
     */
    public function test_trip_request_creation_fails_validation(): void
    {
        $tripData = [
            'destination' => '', // Inválido
            'departure_date' => now()->addDays(5)->format('Y-m-d'), // Válido
            'return_date' => now()->addDays(4)->format('Y-m-d'), // Antes da partida
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->regularToken)
                         ->postJson('/api/trip-requests', $tripData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination', 'return_date']);
    }

    /**
     * Teste de usuário autenticado pode listar suas próprias solicitações de viagem.
     */
    public function test_regular_user_can_list_their_own_trip_requests(): void
    {
        // Criar algumas solicitações para o usuário comum
        TripRequest::factory()->count(3)->create(['user_id' => $this->regularUser->id]);
        // Criar algumas solicitações para outro usuário
        TripRequest::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->regularToken)
                         ->getJson('/api/trip-requests');

        $response->assertStatus(200)
            ->assertJsonCount(3); // Deve ver apenas suas 3 solicitações
    }

    /**
     * Teste de usuário administrador pode listar todas as solicitações de viagem.
     */
    public function test_admin_user_can_list_all_trip_requests(): void
    {
        // Criar algumas solicitações para o usuário comum
        TripRequest::factory()->count(3)->create(['user_id' => $this->regularUser->id]);
        // Criar algumas solicitações para outro usuário
        TripRequest::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                         ->getJson('/api/trip-requests');

        $response->assertStatus(200)
            ->assertJsonCount(5); // Administrador deve ver todas as 5 solicitações
    }

    /**
     * Teste de usuário pode visualizar sua própria solicitação de viagem específica.
     */
    public function test_user_can_view_their_own_trip_request(): void
    {
        $trip = TripRequest::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->regularToken)
                         ->getJson('/api/trip-requests/' . $trip->id);

        $response->assertStatus(200)
            ->assertJson(['id' => $trip->id, 'destination' => $trip->destination]);
    }

    /**
     * Teste de usuário não pode visualizar a solicitação de viagem de outro usuário.
     */
    public function test_user_cannot_view_another_users_trip_request(): void
    {
        $anotherUser = User::factory()->create();
        $trip = TripRequest::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->regularToken)
                         ->getJson('/api/trip-requests/' . $trip->id);

        $response->assertStatus(404); // Não encontrado ou não autorizado
    }

    /**
     * Teste de administrador pode visualizar qualquer solicitação de viagem específica.
     */
    public function test_admin_can_view_any_trip_request(): void
    {
        $trip = TripRequest::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                         ->getJson('/api/trip-requests/' . $trip->id);

        $response->assertStatus(200)
            ->assertJson(['id' => $trip->id, 'destination' => $trip->destination]);
    }

    /**
     * Teste de administrador pode atualizar o status da solicitação de viagem para 'aprovado'.
     */
    public function test_admin_can_update_trip_request_status_to_aprovado(): void
    {
        $trip = TripRequest::factory()->create(['user_id' => $this->regularUser->id]);
        $approvedStatus = TripStatus::where('name', 'aprovado')->first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                         ->patchJson('/api/trip-requests/' . $trip->id . '/status', [
                             'status' => 'aprovado',
                         ]);

        $response->assertStatus(200)
            ->assertJson(['status_id' => $approvedStatus->id]);

        $this->assertDatabaseHas('trip_requests', [
            'id' => $trip->id,
            'status_id' => $approvedStatus->id,
        ]);
    }

    /**
     * Teste de administrador pode atualizar o status da solicitação de viagem para 'cancelado'.
     */
    public function test_admin_can_update_trip_request_status_to_cancelado(): void
    {
        $trip = TripRequest::factory()->create(['user_id' => $this->regularUser->id]);
        $cancelledStatus = TripStatus::where('name', 'cancelado')->first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                         ->patchJson('/api/trip-requests/' . $trip->id . '/status', [
                             'status' => 'cancelado',
                         ]);

        $response->assertStatus(200)
            ->assertJson(['status_id' => $cancelledStatus->id]);

        $this->assertDatabaseHas('trip_requests', [
            'id' => $trip->id,
            'status_id' => $cancelledStatus->id,
        ]);
    }

    /**
     * Teste de usuário comum não pode atualizar o status da solicitação de viagem.
     */
    public function test_regular_user_cannot_update_trip_request_status(): void
    {
        $trip = TripRequest::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->regularToken)
                         ->patchJson('/api/trip-requests/' . $trip->id . '/status', [
                             'status' => 'aprovado',
                         ]);

        $response->assertStatus(403); // Proibido
    }

    /**
     * Teste não pode mudar o status de 'aprovado' para 'cancelado' (regra de negócio).
     */
    public function test_cannot_change_status_from_aprovado_to_cancelado(): void
    {
        $approvedStatus = TripStatus::where('name', 'aprovado')->first();
        $trip = TripRequest::factory()->create(['user_id' => $this->regularUser->id, 'status_id' => $approvedStatus->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                         ->patchJson('/api/trip-requests/' . $trip->id . '/status', [
                             'status' => 'cancelado',
                         ]);

        $response->assertStatus(400); // Requisição inválida
    }

    /**
     * Teste de filtragem de solicitações de viagem por status.
     */
    public function test_can_filter_trip_requests_by_status(): void
    {
        $pendingStatus = TripStatus::where('name', 'solicitado')->first();
        $approvedStatus = TripStatus::where('name', 'aprovado')->first();

        TripRequest::factory()->count(2)->create(['user_id' => $this->adminUser->id, 'status_id' => $pendingStatus->id]);
        TripRequest::factory()->count(3)->create(['user_id' => $this->adminUser->id, 'status_id' => $approvedStatus->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                         ->getJson('/api/trip-requests?status=solicitado');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    /**
     * Teste de filtragem de solicitações de viagem por destino.
     */
    public function test_can_filter_trip_requests_by_destination(): void
    {
        TripRequest::factory()->create(['user_id' => $this->adminUser->id, 'destination' => 'Paris']);
        TripRequest::factory()->create(['user_id' => $this->adminUser->id, 'destination' => 'London']);
        TripRequest::factory()->create(['user_id' => $this->adminUser->id, 'destination' => 'New York']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                         ->getJson('/api/trip-requests?destination=Paris');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['destination' => 'Paris']);
    }

    /**
     * Teste de filtragem de solicitações de viagem por intervalo de datas.
     */
    public function test_can_filter_trip_requests_by_date_range(): void
    {
        TripRequest::factory()->create(['user_id' => $this->adminUser->id, 'departure_date' => '2024-01-01', 'return_date' => '2024-01-10']);
        TripRequest::factory()->create(['user_id' => $this->adminUser->id, 'departure_date' => '2024-02-01', 'return_date' => '2024-02-10']);
        TripRequest::factory()->create(['user_id' => $this->adminUser->id, 'departure_date' => '2024-03-01', 'return_date' => '2024-03-10']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                         ->getJson('/api/trip-requests?from=2024-01-15&to=2024-02-15');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['departure_date' => '2024-02-01T00:00:00.000000Z']);
    }
}