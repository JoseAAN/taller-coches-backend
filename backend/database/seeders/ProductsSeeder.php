<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definimos una lista de productos con su categoría correspondiente
        $products = [
            // --- Limpieza (Conservando algunos) ---
            [
                'name' => 'Champú Coche Ultra Brillo (500ml)',
                'description' => 'Champú concentrado con pH neutro. Genera mucha espuma y deja un acabado brillante sin dañar la cera existente.',
                'price' => 12.50,
                'stock' => 2,
                'category' => 'Limpieza',
            ],
            [
                'name' => 'Cera Líquida Premium (Spray 500ml)',
                'description' => 'Cera rápida en spray de fácil aplicación. Proporciona protección duradera y un brillo profundo en minutos.',
                'price' => 18.99,
                'stock' => 30,
                'category' => 'Limpieza',
            ],
            [
                'name' => 'Limpiador de Llantas Extremo',
                'description' => 'Fórmula avanzada que disuelve el polvo de frenos y la suciedad de la carretera al contacto.',
                'price' => 14.25,
                'stock' => 45,
                'category' => 'Limpieza',
            ],
            
            // --- Aceites (Nuevos) ---
            [
                'name' => 'Aceite Sintético 5W-30 Long Life (5L)',
                'description' => 'Aceite de motor totalmente sintético de alto rendimiento. Protege contra el desgaste y mejora la eficiencia del combustible.',
                'price' => 45.90,
                'stock' => 20,
                'category' => 'Aceites',
            ],
            [
                'name' => 'Filtro de Aceite Universal',
                'description' => 'Filtro blindado de alta capacidad de retención. Mantiene el circuito de lubricación libre de impurezas.',
                'price' => 10.50,
                'stock' => 100,
                'category' => 'Aceites',
            ],

            // --- Frenos (Nuevos) ---
            [
                'name' => 'Juego de Pastillas de Freno Delanteras',
                'description' => 'Pastillas de compuesto cerámico. Frenada silenciosa, baja generación de polvo y excelente mordida en frío y caliente.',
                'price' => 35.00,
                'stock' => 15,
                'category' => 'Frenos',
            ],
            [
                'name' => 'Líquido de Frenos DOT 4 (500ml)',
                'description' => 'Fluido sintético de alto punto de ebullición para sistemas de frenos hidráulicos y embragues.',
                'price' => 8.95,
                'stock' => 2,
                'category' => 'Frenos',
            ],

            // --- Suspensión (Nuevos) ---
            [
                'name' => 'Amortiguador de Gas Trasero',
                'description' => 'Amortiguador bitubo de presión de gas. Restaura el control y la estabilidad original del vehículo.',
                'price' => 65.00,
                'stock' => 1,
                'category' => 'Suspensión',
            ],

            // --- Motor (Nuevos) ---
            [
                'name' => 'Bujías de Iridio (Pack 4)',
                'description' => 'Bujías de alto rendimiento con electrodo central de iridio. Mayor durabilidad y mejor arranque.',
                'price' => 42.00,
                'stock' => 25,
                'category' => 'Motor',
            ],
             [
                'name' => 'Kit Correa Distribución',
                'description' => 'Kit completo con tensor y rodillos. Esencial para el mantenimiento preventivo del motor.',
                'price' => 110.00,
                'stock' => 5,
                'category' => 'Motor',
            ],
        ];

        foreach ($products as $data) {
            // Extraer el nombre de la categoría del array de datos
            $categoryName = $data['category'];
            unset($data['category']); // Lo quitamos para que no falle al crear el producto

            // Crear el producto
            $product = Product::create($data);

            // Buscar la categoría (aseguramos que exista, aunque el CategoriesSeeder ya debió correr)
            $category = Category::firstOrCreate(['name' => $categoryName]);

            // Asociar
            $product->categories()->attach($category->id);
        }

        // Crear productos aleatorios extra y asignarles categorías al azar
        // Solo usar las 5 categorías reales (no las generadas por factory)
        $realCategories = Category::whereIn('name', ['Limpieza', 'Aceites', 'Frenos', 'Suspensión', 'Motor'])->get();
        $randomProducts = Product::factory()->count(10)->create();

        foreach ($randomProducts as $product) {
            // Asignar entre 1 y 2 categorías reales aleatorias a cada producto
            $randomCats = $realCategories->random(rand(1, 2));
            $product->categories()->attach($randomCats->pluck('id'));
        }
    }
}
