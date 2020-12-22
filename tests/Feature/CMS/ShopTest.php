<?php

namespace Tests\Feature\CMS;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $admin;

    public function setup(): void
    {
        parent::setup();
        $this->admin = User::factory([
            'admin' => true
        ])->create();
    }

    public function test_can_create_a_shop()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );

        $response = $this->json('post', route('shops.store'), [
            'name' => $name = $this->faker->words(2, true),
            'domain' => $domain = $this->faker->domainName,
            'active' => true
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('shops', [
            'name' => $name,
            'domain' => $domain
        ]);
        $response->assertJsonFragment([
            'name' => $name
        ]);
    }

    public function test_update_a_shop()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );

        $shop = Shop::factory()->create();

        $response = $this->json('patch', route('shops.update', $shop->id), [
            'name' => 'New Shop Name',
            'domain' => 'safedomain.com',
            'active' => false
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'uuid' => $shop->uuid,
            'name' => 'New Shop Name',
            'domain' => 'safedomain.com'
        ]);
    }

    public function test_can_get_paginated_list_of_shops()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );
        Shop::factory(50)->create();
        $response = $this->json('get', route('shops.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'uuid',
                    'domain',
                    'active',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
    }
}
