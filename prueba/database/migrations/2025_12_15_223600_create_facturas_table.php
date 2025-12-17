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
    Schema::create('facturas', function (Blueprint $table) {
        $table->id();
        $table->string('numero_factura');
        $table->decimal('total', 8, 2);
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        // ESTA ES LA LÍNEA MÁGICA:
        // Crea automáticamente dos columnas:
        // 1. facturable_id (bigint)
        // 2. facturable_type (string)
        $table->morphs('facturable'); 

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
