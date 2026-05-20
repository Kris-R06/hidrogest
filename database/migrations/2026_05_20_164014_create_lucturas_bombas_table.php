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
        Schema::create('lecturas_bombas', function (Blueprint $table) {
            $table->id();
            // Llave foránea para vincularla a la bomba
            $table->foreignId('bomba_id')->constrained('bombas')->onDelete('cascade');
            
            // Variables del sensor
            $table->float('ph')->nullable();
            $table->float('turb')->nullable(); // Turbidez
            $table->float('ppm')->nullable(); // Sólidos disueltos
            $table->float('temp')->nullable(); // Temperatura
            $table->float('flujo')->nullable(); // Flujo L/s
            $table->float('presion')->nullable(); // Presión PSI
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturas_bombas');
    }
};
