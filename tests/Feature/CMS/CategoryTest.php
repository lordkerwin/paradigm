<?php

namespace Tests\Feature\CMS;

use App\Models\Category;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    public function test_can_create_a_category()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );

        $response = $this->json('post', route('categories.store'), [
            'name' => $name = $this->faker->words(2, true),
            'slug' => $slug = $this->faker->word . '-' . $this->faker->word,
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', [
            'name' => $name,
            'slug' => $slug
        ]);
        $response->assertJsonFragment([
            'name' => $name
        ]);
    }

    public function test_can_update_a_category()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );

        $category = Category::factory()->create();

        $response = $this->json('patch', route('categories.update', $category->id), [
            'name' => 'My Updated Category',
            'slug' => 'my-updated-category'
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'My Updated Category',
            'slug' => 'my-updated-category'
        ]);
    }

    public function test_cannot_create_two_categories_with_same_name()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );

        Category::factory([
            'name' => $name = 'My First Category',
            'slug' => $slug = 'my-first-category'
        ])->create();

        $response = $this->json('post', route('categories.store'), [
            'name' => $name,
            'slug' => 'a-different-slug'
        ]);
        $response->assertStatus(422);
        $response->assertJsonFragment([
            "The name has already been taken."
        ]);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_cannot_create_two_categories_with_same_slug()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );

        Category::factory([
            'name' => $name = 'My First Category',
            'slug' => $slug = 'my-first-category'
        ])->create();

        $response = $this->json('post', route('categories.store'), [
            'name' => 'Some other name',
            'slug' => $slug
        ]);
        $response->assertStatus(422);
        $response->assertJsonFragment([
            "The slug has already been taken."
        ]);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_can_get_paginated_list_of_categories()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );
        Category::factory(50)->create();
        $response = $this->json('get', route('categories.index'));
        dd($response);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'created_at',
                    'updated_at',
                ]
            ],
            'meta' => [
                'current_page',
                'from',
                'path',
                'per_page',
                'to',
                'success',
                'message',
            ]
        ]);
    }

    public function test_can_get_a_single_category()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );
        $category = Category::factory()->create();
        $response = $this->json('get', route('categories.show', $category->id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_can_attach_category_to_shop()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );
        $category = Category::factory()->create();
        $shop = Shop::factory()->create();

        $response = $this->json('post', route('categories.attach-to-shop', $category->id), [
            'shop_id' => $shop->id
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('category_shop', [
            'category_id' => $category->id,
            'shop_id' => $shop->id,
            'active' => true
        ]);
    }
}
