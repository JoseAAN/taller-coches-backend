<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla de Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'admin', 'client'
            $table->timestamps();
        });

        // 2. Tabla de Usuarios
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('dni')->unique()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable(); 
            $table->text('address')->nullable(); 
            
            // Clave Foránea a Roles
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            
            // Seguridad de Inicio de Sesión
            $table->integer('login_attempts')->default(0); 
            $table->boolean('blocked')->default(false); 
            $table->timestamp('unblock_time')->nullable(); 

            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_code')->nullable();
            $table->string('password')->nullable();
            $table->string('api_token', 80)->nullable()->unique();
            $table->string('google_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Tabla de Logs de Usuario
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('login_time')->nullable();
            $table->timestamp('logout_time')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles'); // Drop roles after users
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
