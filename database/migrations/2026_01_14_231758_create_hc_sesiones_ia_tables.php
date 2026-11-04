<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bloque P: Tratamiento (Por sesión con firma del profesional)
        Schema::create('hc_sesiones_tratamiento', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->foreignId('cita_id')
                  ->nullable()
                  ->constrained('citas')
                  ->nullOnDelete();

            $table->foreignId('profesional_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            $table->smallInteger('numero_sesion')->default(1);
            $table->date('fecha')->useCurrent();

            // Campos exactos del Bloque P del formulario 033
            $table->text('diagnosticos_complicaciones')->nullable();
            $table->text('procedimientos')->nullable();
            $table->jsonb('procedimientos_detalle')->nullable();
            $table->text('prescripciones')->nullable(); // Fármacos, dosis, indicaciones

            // Firma de la sesión
            $table->string('codigo_profesional', 50)->nullable();
            $table->string('firma_profesional', 255)->nullable();
            $table->timestamp('firmado_at')->nullable();

            $table->timestamps();
        });

        // Capa IA (Dictado por voz + Extracción estructurada human-in-the-loop)
        Schema::create('ia_dictados', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->foreignId('profesional_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            $table->string('audio_path', 500)->nullable();
            $table->text('transcripcion')->nullable();
            $table->string('proveedor_stt', 50)->nullable();
            $table->smallInteger('duracion_seg')->nullable();
            $table->boolean('speaker_verificado')->default(false);
            $table->decimal('speaker_score', 5, 4)->nullable();
            $table->string('estado', 30)->default('procesado');

            $table->timestamps();
        });

        Schema::create('ia_extracciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dictado_id')
                  ->constrained('ia_dictados')
                  ->onDelete('cascade');

            $table->string('bloque_destino', 50); // anamnesis, signos_vitales, odontograma, diagnosticos, etc.
            $table->jsonb('valor_propuesto');
            $table->decimal('confianza', 5, 4)->nullable();
            $table->string('estado', 25)->default('pendiente'); // pendiente, aceptado, editado, rechazado
            $table->jsonb('valor_final')->nullable();

            $table->foreignId('revisado_por')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            $table->string('modelo_llm', 80)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_extracciones');
        Schema::dropIfExists('ia_dictados');
        Schema::dropIfExists('hc_sesiones_tratamiento');
    }
};

