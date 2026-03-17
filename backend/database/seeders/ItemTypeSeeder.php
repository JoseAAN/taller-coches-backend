<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemType;

class ItemTypeSeeder extends Seeder
{
    public function run(): void
    {
        ItemType::updateOrCreate(['id' => 1], ['name' => 'PRODUCT']);
        ItemType::updateOrCreate(['id' => 2], ['name' => 'SERVICE']);
    }
}
