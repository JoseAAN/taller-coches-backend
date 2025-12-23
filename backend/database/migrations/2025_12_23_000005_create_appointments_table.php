<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            
            $table->dateTime('appointment_date');
            $table->decimal('final_price', 10, 2)->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
