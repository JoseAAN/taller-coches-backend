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
            'id' => 1,
            'label' => 'Configuration',
            'icon'  => 'settings',
            'route' => null,
            'order' => 9999,
            'is_active' => true
        ]);

        // Creamos el ítem HIJO vinculado al padre
        AdminNavigationItem::factory()->create([
            'label' => 'Admin sidebar configuration',
            'icon'  => 'build_circle',
            'route' => '/admin/admin-sidebar-configuration',
            'parent_id' => $father->id,
            'order' => 1,
            'is_active' => true
        ]);
    }
}
