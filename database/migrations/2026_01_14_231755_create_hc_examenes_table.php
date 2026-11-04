<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hc_examenes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->foreignId('profesional_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            // Bloque L: Plan de Diagnóstico y Bloque M: Exámenes
            // Tipos MSP: biometria, quimica_sanguinea, rayos_x, otros
            $table->string('tipo', 50);
            $table->string('nombre_examen', 255)->nullable();
            $table->text('pedido_descripcion')->nullable();
            $table->text('informe_resultado')->nullable();
            $table->string('archivo_ruta', 500)->nullable();
            $table->string('estado', 20)->default('solicitado'); // solicitado | informado
            $table->date('fecha_solicitud')->useCurrent();
            $table->date('fecha_resultado')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hc_examenes');
    }
};

