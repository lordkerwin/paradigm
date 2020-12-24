<?php

namespace Tests\Feature\CMS;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
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

    public function test_can_create_a_product()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );

        // make a product, (but dont store it in the database yet)
        $product = Product::factory()->make();

        // submit product to store method
        $response = $this->json('post', route('products.store'), $product->toArray());
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => $product->name,
            'slug' => $product->slug
        ]);
    }

    public function test_can_update_a_product()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );

        $product = Product::factory()->create();

        $response = $this->json('patch', route('products.update', $product->id), [
            'name' => 'My Updated Product',
            'slug' => 'my-updated-product'
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'My Updated Product',
            'slug' => 'my-updated-product'
        ]);
    }

    public function test_can_get_paginated_list_of_products()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );
        Product::factory(50)->create();
        $response = $this->json('get', route('products.index'));
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
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $shop = Shop::factory()->create();
        $product->shops()->attach($shop->id);
        $product->categories()->attach($category->id);
        $response = $this->json('get', route('products.show', $product->id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'slug',
                'created_at',
                'updated_at',
                'shops' => [
                    '*' => [
                        'id',
                        'name',
                        'uuid',
                        'domain',
                        'active',
                    ]
                ],
                'categories' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                    ]
                ]
            ],
        ]);
    }


    public function test_can_attach_product_to_shop()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );
        $shop = Shop::factory()->create();
        $product = Product::factory()->create();

        $response = $this->json('post', route('products.attach-to-shop', $product->id), [
            'shop_id' => $shop->id
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('product_shop', [
            'product_id' => $product->id,
            'shop_id' => $shop->id,
        ]);
    }

    public function test_can_attach_product_to_category()
    {
        $this->withoutExceptionHandling();
        Sanctum::actingAs(
            $this->admin,
            ['*']
        );
        $category = Category::factory()->create();
        $product = Product::factory()->create();

        $response = $this->json('post', route('products.attach-to-category', $product->id), [
            'category_id' => $category->id
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('category_product', [
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);
    }
}
