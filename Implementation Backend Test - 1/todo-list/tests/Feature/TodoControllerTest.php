<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Todo;
use App\Models\User;

class TodoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Register and login the user to get the token
        $response = $this->postJson('/api/register', [
            'name' => 'coba',
            'email' => 'coba@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201);

        $response = $this->postJson('/api/login', [
            'email' => 'coba@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        $this->token = $response->json('token');

        // Authenticate user using Sanctum token
        $this->actingAs($this->user, 'api');
    }

    public function testIndex()
    {
        $response = $this->getJson('/api/todos');
        $response->assertStatus(200);
    }

    public function testStoreSuccess()
    {
        $data = [
            'title' => 'Test Todo',
            'description' => 'This is a test todo.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/todos', $data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('todos', $data);
    }

    public function testStoreValidationError()
    {
        $data = [
            'title' => '',
            'description' => 'This is a test todo.',
        ];

        $response = $this->postJson('/api/todos', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function testShow()
    {
        $todo = Todo::factory()->create();

        $response = $this->getJson("/api/todos/$todo->id");
        $response->assertStatus(200);
    }

    public function testUpdateSuccess()
    {
        $todo = Todo::factory()->create();

        $data = [
            'title' => 'Updated Todo',
            'description' => 'This is an updated test todo.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/todos/$todo->id", $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('todos', $data);
    }

    public function testUpdateNotFoundError()
    {
        $data = [
            'title' => 'Updated Todo',
            'description' => 'This is an updated test todo.',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/todos/999', $data);
        $response->assertStatus(404);
    }

    public function testDelete()
    {
        $todo = Todo::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/todos/$todo->id");
        $response->assertStatus(204);
        $this->assertDeleted($todo);
    }

    public function testDeleteNotFoundError()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson('/api/todos/999');
        $response->assertStatus(404);
    }
}
