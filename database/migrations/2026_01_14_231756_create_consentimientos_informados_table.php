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
        Schema::create('consentimientos_informados', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('historia_id')->nullable()->constrained('historias_clinicas')->nullOnDelete();
            $table->foreignId('plan_tratamiento_id')->nullable()->constrained('planes_tratamiento')->nullOnDelete();
            $table->foreignId('profesional_id')->constrained('usuarios')->restrictOnDelete();

            $table->string('tipo_consentimiento', 50);
            $table->string('origen_documento', 30)->default('escaneado_fisico');
            $table->string('estado', 20)->default('firmado');

            $table->string('archivo_ruta', 500);
            $table->string('archivo_nombre', 255);
            $table->string('sha256_hash', 64)->nullable();
            $table->bigInteger('tamanio_bytes')->nullable();

            $table->string('firmado_por', 30)->default('paciente');
            $table->string('nombre_firmante', 255);
            $table->string('cedula_firmante', 10);
            $table->string('parentesco', 100)->nullable();
            $table->date('fecha_firma')->default(now());

            $table->text('observaciones')->nullable();
            $table->jsonb('datos_ocr')->nullable();

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
        Schema::dropIfExists('consentimientos_informados');
    }
};
