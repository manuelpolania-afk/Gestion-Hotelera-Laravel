<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nombre_habitacion', 50);
            $table->unsignedInteger('capacidad');
            $table->decimal('valor', 10, 2);
            $table->enum('estado', ['Disponible', 'Ocupada', 'Mantenimiento', 'Fuera de servicio'])
                  ->default('Disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
