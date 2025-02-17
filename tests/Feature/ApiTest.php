<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class ApiTest extends TestCase
{

    private $token;

    public function test_create_user_return_successful_response(): void
    {
        $response = $this->postJson('/api/user', [
            'name' => 'Test User',
            'email' => 'test@phpunit.com',
            'password' => 'phpunit123',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_login_returns_successful_response(): string
    {
        $email = 'test@example.com';
        $password = 'password123';

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'token',
        ]);

        return $response->json('token');
    }

    #[Depends('test_login_returns_successful_response')]
    public function test_endpoint_get_character_returns_successful_response(string $token): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/characters/1');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'id',
            'height',
            'mass',
            'hair_color',
            'skin_color',
            'eye_color',
            'birth_year',
            'gender',
            'created_at',
            'updated_at',
            'planet',
            'films',
            'vehicles',
            'species',
        ]);
    }

    #[Depends('test_login_returns_successful_response')]
    public function test_endpoint_get_planet_returns_successful_response(string $token): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/planets/1');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'id',
            'name',
            'rotation_period',
            'orbital_period',
            'diameter',
            'climate',
            'gravity',
            'terrain',
            'surface_water',
            'population',
            'created_at',
            'updated_at',
            'characters',
        ]);
    }

    #[Depends('test_login_returns_successful_response')]
    public function test_endpoint_get_film_returns_successful_response(string $token): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/films/1');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'title',
            'opening_crawl',
            'director',
            'producer',
            'release_date',
            'id',
            'updated_at',
            'created_at',
            'characters',
            'planets',
        ]);
    }

    #[Depends('test_login_returns_successful_response')]
    public function test_endpoint_logout_returns_successful_response(string $token): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/logout');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'message',
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->call('migrate:fresh');
        $kernel->call('db:seed --class=UserSeeder');
        Redis::flushall();
        parent::tearDownAfterClass();
    }

}
