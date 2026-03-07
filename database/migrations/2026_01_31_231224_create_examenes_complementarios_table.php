<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examenes_complementarios', function (Blueprint $table) {
            $table->id();

            // Relación con historia clínica
            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            // Tipo de examen
            $table->enum('tipo_examen', [
                'radiografia_periapical',
                'radiografia_panoramica',
                'radiografia_oclusal',
                'biometria_hematica',
                'glucosa',
                'laboratorio_clinico',
                'biopsia',
                'interconsulta',
                'otro'
            ]);

            // Detalles del examen
            $table->string('nombre_examen');
            $table->text('descripcion')->nullable();
            
            // Fechas
            $table->date('fecha_solicitud');
            $table->date('fecha_resultado')->nullable();

            // Resultados
            $table->text('resultados')->nullable();
            $table->string('archivo_resultado')->nullable()->comment('Ruta al archivo PDF/imagen del resultado');

            // Estado
            $table->enum('estado', ['solicitado', 'en_proceso', 'completado', 'cancelado'])->default('solicitado');

            // Auditoría
            $table->foreignId('profesional_solicita')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('examenes_complementarios');
    }
};
