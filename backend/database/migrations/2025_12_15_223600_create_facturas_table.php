<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_create_facturas_table.php
    public function up() {
        // Tabla para Facturas de Productos (Pedidos)
        Schema::create('facturas_productos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_factura');
            $table->decimal('total', 8, 2);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Relación específica con Pedidos
            $table->foreignId('pedido_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Tabla para Facturas de Servicios (Citas/Taller)
        Schema::create('facturas_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('numero_factura');
            $table->decimal('total', 8, 2);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Relación específica con Citas
            $table->foreignId('cita_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_productos');
        Schema::dropIfExists('facturas_servicios');
    }
};
