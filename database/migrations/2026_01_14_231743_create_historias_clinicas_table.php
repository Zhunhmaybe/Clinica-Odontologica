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

            // Bloque A: Datos del Establecimiento y Usuario/Paciente
            $table->string('institucion_sistema', 150)->default('RED PRIVADA COMPLEMENTARIA');
            $table->string('unicodigo', 30)->nullable();
            $table->string('establecimiento_salud', 150)->default('DANNY LARA DENTAL SOLUTIONS');
            $table->string('numero_historia', 50)->unique();
            $table->string('numero_archivo', 50)->nullable();
            $table->integer('numero_hoja')->default(1);

            // Foto de edad al momento de la apertura (Instructivo Bloque A)
            $table->smallInteger('edad_al_abrir')->nullable();
            $table->string('condicion_edad', 5)->default('A'); // H=Horas, D=Días, M=Meses, A=Años

            // Bloque B: Embarazada (sí/no)
            $table->boolean('embarazada')->default(false);

            // Fechas y vigencia de 365 días (Regla del instructivo MSP)
            $table->date('fecha_atencion')->useCurrent();
            $table->time('hora_apertura')->nullable();
            $table->date('vigente_hasta')->nullable();

            // Estado: borrador, abierta, cerrada, vencida
            $table->string('estado', 20)->default('abierta');
            $table->string('schema_version', 20)->default('033/2021');

            // Bloque O: Datos del Profesional y Firma
            $table->timestamp('firmado_at')->nullable();
            $table->string('firma_hash', 128)->nullable();
            $table->string('codigo_profesional', 50)->nullable();

            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['paciente_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};
