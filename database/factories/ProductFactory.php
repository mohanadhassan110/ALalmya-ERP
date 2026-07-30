<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'category_id' => Category::factory(),
            'cost_price' => $cost = fake()->randomFloat(2, 50, 2000),
            'wholesale_price' => round($cost * 1.2, 2),
            'retail_price' => round($cost * 1.5, 2),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'description' => fake()->optional()->sentence(),
        ];
    }

    /** Product with guaranteed stock */
    public function inStock(int $qty = 20): static
    {
        return $this->state(['stock_quantity' => $qty]);
    }

    /** Product with zero stock */
    public function outOfStock(): static
    {
        return $this->state(['stock_quantity' => 0]);
    }
}
