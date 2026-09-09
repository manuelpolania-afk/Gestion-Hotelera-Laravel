<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->restrictOnDelete();
            $table->date('fecha');
            $table->enum('estado_reserva', ['Pendiente', 'Confirmada', 'Cancelada', 'Finalizada'])
                  ->default('Pendiente');
            $table->decimal('sub_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
