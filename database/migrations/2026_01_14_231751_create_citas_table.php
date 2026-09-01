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
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->restrictOnDelete();

            $table->foreignId('doctor_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->foreignId('especialidad_id')
                ->constrained('especialidades')
                ->restrictOnDelete();

            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->string('motivo', 500)->nullable();
            $table->text('notas_internas')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('citas');
    }
};
