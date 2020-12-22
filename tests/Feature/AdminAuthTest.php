<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{

    use RefreshDatabase, WithFaker;
    public function setup(): void
    {
        parent::setup();
    }

    public function test_admin_user_can_login()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create([
            'admin' => true
        ]);

        $response = $this->json('post', route('cms.auth.login'), [
            'email' => $user->email,
            'password' => 'password'
        ]);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'meta' => [
                'success',
                'message'
            ],
            'data' => [
                'token',
                'user' => [
                    'name',
                    'email'
                ]
            ]
        ]);
    }

    public function test_non_admin_user_cannot_login()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create([
            'admin' => false
        ]);

        $response = $this->json('post', route('cms.auth.login'), [
            'email' => $user->email,
            'password' => 'password'
        ]);
        $response->assertForbidden();
        $response->assertJson([
            'success' => false,
            'message' => 'Forbidden'
        ]);
    }
}
