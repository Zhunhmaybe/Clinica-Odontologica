<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hc_signos_vitales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->foreignId('profesional_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            // Bloque F: Constantes Vitales
            $table->smallInteger('presion_sistolica')->nullable();
            $table->smallInteger('presion_diastolica')->nullable();
            $table->string('presion_arterial', 20)->nullable(); // Ej. 120/80
            $table->smallInteger('pulso')->nullable(); // Frecuencia cardíaca (lpm)
            $table->decimal('temperatura', 4, 1)->nullable(); // °C
            $table->smallInteger('frecuencia_respiratoria')->nullable(); // min.

            $table->timestamp('tomado_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hc_signos_vitales');
    }
};

