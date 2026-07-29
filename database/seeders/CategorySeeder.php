<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'طقم سرير', 'description' => 'أطقم سرير كاملة'],
            ['name' => 'فوطة', 'description' => 'فوط حمام ووجه'],
            ['name' => 'لحاف', 'description' => 'لحف بجميع المقاسات'],
            ['name' => 'بطانية', 'description' => 'بطاطين شتوية وصيفية'],
            ['name' => 'كفرتة', 'description' => 'كفرتات سرير'],
            ['name' => 'كفرتة زينة', 'description' => 'كفرتات زينة ديكور'],
            ['name' => 'مفرش قطيفة', 'description' => 'مفارش قطيفة فاخرة'],
            ['name' => 'سجادة', 'description' => 'سجاد بجميع المقاسات'],
            ['name' => 'دفاية', 'description' => 'دفايات شتوية'],
            ['name' => 'برنص', 'description' => 'برانص حمام'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
