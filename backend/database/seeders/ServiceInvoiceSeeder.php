<?php

namespace Database\Seeders;

use App\Models\ServiceInvoice;
use Database\Factories\ServicieInvoiceFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        ServiceInvoice::factory()->count(50)->create();
    }
}
