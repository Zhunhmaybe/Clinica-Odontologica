<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('paciente_id')
                  ->constrained('pacientes')
                  ->restrictOnDelete();
                  
            $table->foreignId('profesional_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            $table->string('numero_historia', 50)->unique()->nullable();
            $table->date('fecha_atencion')->useCurrent();
            $table->string('estado', 20)->default('abierta');

            $table->text('motivo_consulta')->nullable();
            $table->text('enfermedad_actual')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};
