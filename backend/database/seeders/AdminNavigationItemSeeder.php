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
        $items = [
            // --- PADRES (solamente desplegables) ---
            ['id' => 1, 'label' => 'Products', 'icon' => 'inventory_2', 'route' => null, 'parent_id' => null, 'order' => 1],
            ['id' => 3, 'label' => 'Services', 'icon' => 'inventory_2', 'route' => null, 'parent_id' => null, 'order' => 2],
            ['id' => 5, 'label' => 'Configuration', 'icon' => 'build', 'route' => null, 'parent_id' => null, 'order' => 9999],

            // --- HIJOS ---
            ['id' => 2, 'label' => 'Product', 'icon' => 'home_repair_service', 'route' => '/admin/products', 'parent_id' => 1, 'order' => 1],
            ['id' => 4, 'label' => 'Service Home View', 'icon' => 'home_repair_service', 'route' => '/admin/service-home-edit', 'parent_id' => 3, 'order' => 1],
            ['id' => 6, 'label' => 'Admin sidebar configuration', 'icon' => 'build_circle', 'route' => '/admin/admin-sidebar-configuration', 'parent_id' => 5, 'order' => 1],
            ['id' => 24, 'label' => 'Test config', 'icon' => 'settings', 'route' => '/admin/testing', 'parent_id' => 5, 'order' => 0],
            ['id' => 25, 'label' => 'Services Types', 'icon' => 'local_car_wash', 'route' => '/admin/services-types', 'parent_id' => 3, 'order' => 3],
            ['id' => 26, 'label' => 'Services', 'icon' => 'dynamic_form', 'route' => '/admin/services', 'parent_id' => 3, 'order' => 2],
        ];

        foreach ($items as $item) {
            AdminNavigationItem::factory()->create(array_merge($item, [
                'is_active' => true
            ]));
        }
    }
}