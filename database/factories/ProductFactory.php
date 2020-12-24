<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $name = $this->faker->words(3, true),
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'active' => $this->faker->randomElement([true, false]),
            'quantity' => $this->faker->numberBetween(1, 50),
            'reserve_quantity' => $this->faker->numberBetween(2, 10),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'weight_grams' => $this->faker->randomFloat(2,100, 500),
            'image' => $this->faker->imageUrl()
        ];
    }
}

