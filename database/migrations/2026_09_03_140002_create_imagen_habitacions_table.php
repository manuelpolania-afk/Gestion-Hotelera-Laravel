<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagenes_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->string('ruta');
            $table->boolean('principal')->default(false);
            $table->timestamp('fecha_subida')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagenes_habitacion');
    }
};
