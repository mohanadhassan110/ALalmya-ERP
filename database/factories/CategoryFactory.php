<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'مفروشات غرف النوم', 'مفروشات غرف المعيشة', 'مفروشات أطفال',
                'ستائر', 'سجاد', 'لحافات', 'مراتب', 'مخدات',
            ]),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
