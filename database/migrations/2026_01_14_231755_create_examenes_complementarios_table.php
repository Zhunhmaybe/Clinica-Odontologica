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

            // Auditoría
            $table->foreignId('profesional_solicita')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            // Tipo de examen
            $table->string('tipo_examen', 50);

            // Detalles del examen
            $table->string('nombre_examen', 255);
            $table->text('descripcion')->nullable();
            
            // Fechas
            $table->date('fecha_solicitud')->default(now());
            $table->date('fecha_resultado')->nullable();

            // Resultados
            $table->text('resultados')->nullable();
            $table->string('archivo_resultado', 500)->nullable();

            // Estado
            $table->string('estado', 20)->default('solicitado');

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
