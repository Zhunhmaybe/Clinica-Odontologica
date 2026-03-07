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
                ->cascadeOnDelete();

            $table->foreignId('doctor_id')
                ->constrained('usuarios')
                ->cascadeOnDelete();

            $table->foreignId('especialidad_id')
                ->constrained('especialidades')
                ->cascadeOnDelete();

            $table->timestamp('fecha_inicio');
            $table->string('estado', 20)->default('pendiente');
            $table->string('motivo', 255)->nullable();

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
        Schema::dropIfExists('citas');
    }
};
