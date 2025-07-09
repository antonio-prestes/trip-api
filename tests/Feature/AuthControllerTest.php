<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Teste de sucesso de registro de usuário
     */
    public function test_user_can_register(): void
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }

    /**
     * Teste de falha de registro com erros de validação
     */
    public function test_registration_fails_on_validation_error(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => $this->faker->name,
            'email' => 'not-an-email', // Invalid email
            'password' => '123', // Too short
        ]);

        $response->assertStatus(422);
    }

    /**
     * Teste de sucesso de login de usuário
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $credentials = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'name', 'email'],
            ]);
    }

    /**
     * Teste de falha de login com credenciais inválidas
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $credentials = [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(401);
    }

    /**
     * Teste de usuário autenticado pode recuperar seus dados
     */
    public function test_authenticated_user_can_get_their_data(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ]);
    }

    /**
     * Teste de usuário pode fazer logout
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Fazer logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Successfully logged out',
                 ]);

        // Verificar se o token foi invalidado
        $this->withHeader('Authorization', 'Bearer ' . $token)
             ->getJson('/api/user')
             ->assertStatus(401);
    }

    /**
     * Teste de token pode ser atualizado
     */
    public function test_token_can_be_refreshed(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user',
            ]);

        // O token original deve ser invalidado
        $this->withHeader('Authorization', 'Bearer ' . $token)
             ->getJson('/api/user')
             ->assertStatus(401);

        // O novo token deve funcionar
        $newToken = $response->json('access_token');
        $this->withHeader('Authorization', 'Bearer ' . $newToken)
             ->getJson('/api/user')
             ->assertStatus(200);
    }
}
