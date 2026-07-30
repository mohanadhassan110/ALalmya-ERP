<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 10, 500),
            'reason' => fake()->sentence(3),
            'expense_date' => now()->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
