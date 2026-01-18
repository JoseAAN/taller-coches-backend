<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductInvoice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        ProductInvoice::factory()->count(10)->create();
    }
}
