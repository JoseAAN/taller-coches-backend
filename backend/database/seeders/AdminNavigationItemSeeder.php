<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminNavigationItem;

class AdminNavigationItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $father = AdminNavigationItem::factory()->create([
            'label' => 'Test',
            'icon'  => 'inventory_2',
            'route' => null,
            'order' => 1
        ]);

        // Creamos el ítem HIJO vinculado al padre
        AdminNavigationItem::factory()->create([
            'label' => 'Product',
            'icon'  => 'fragrance',
            'route' => '/admin/products',
            'parent_id' => $father->id,
            'order' => 1
        ]);
    }
}
