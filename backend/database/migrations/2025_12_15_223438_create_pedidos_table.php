<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up() {
    Schema::create('pedidos', function (Blueprint $table) {
    $table->id();
    // Añadimos la relación con el usuario
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
    $table->decimal('total', 8, 2);
    $table->string('estado')->default('pagado');
    $table->timestamps();
});
}

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
