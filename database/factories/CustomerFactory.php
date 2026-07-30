<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'type' => 'retail',
            'balance' => 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function wholesale(): static
    {
        return $this->state(['type' => 'wholesale']);
    }

    public function withDebt(float $amount): static
    {
        return $this->state(['balance' => $amount]);
    }

    public function withCredit(float $amount): static
    {
        return $this->state(['balance' => -$amount]);
    }
}
