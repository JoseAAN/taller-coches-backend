<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The vehicle already has the user, but we can keep it for redundant quick access if needed. But usually linked via vehicle. Let's keep it if strict 1:1 user-appointment logic, but vehicle is better.
            // Following diagram: Cita -> Vehiculo, Cita -> Servicio.
            // Diagram doesn't explicitly show user_id in Citas, but usually it's there. However, given vehicle has user, it's transitive.
            
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            
            $table->dateTime('appointment_date');
            $table->decimal('final_price', 10, 2)->nullable(); // To store agreed price
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
