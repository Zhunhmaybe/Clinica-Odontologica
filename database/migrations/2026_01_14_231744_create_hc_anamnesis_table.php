<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hc_anamnesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')
                  ->unique()
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            // Bloque B: Motivo de Consulta (texto cifrado en modelo)
            $table->text('motivo_consulta')->nullable();

            // Bloque C: Enfermedad o Problema Actual (texto cifrado + estructurado para IA)
            $table->text('enfermedad_actual')->nullable();
            $table->jsonb('enfermedad_actual_estructurada')->nullable();

            // Bloque D: Antecedentes Patológicos Personales (JSONB códigos 1..10)
            $table->jsonb('antecedentes_personales')->nullable();

            // Bloque E: Antecedentes Patológicos Familiares (JSONB códigos 1..10)
            $table->jsonb('antecedentes_familiares')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hc_anamnesis');
    }
};

