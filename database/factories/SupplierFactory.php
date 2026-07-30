<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'initial_balance' => 0,
            'current_balance' => 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function withBalance(float $amount): static
    {
        return $this->state([
            'initial_balance' => $amount,
            'current_balance' => $amount,
        ]);
    }
}
