<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        // Facturas de Carrito
        Schema::create('cart_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->decimal('total', 8, 2);
            
            // Relación con Carrito (1:1)
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });

        // Facturas de Servicio
        Schema::create('service_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->decimal('total', 8, 2);
            // Relación con Cita (1:1)
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_invoices');
        Schema::dropIfExists('service_invoices');
    }
};
