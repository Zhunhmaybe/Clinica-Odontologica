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

            // Relación con paciente y historia
            $table->foreignId('paciente_id')
                  ->constrained('pacientes')
                  ->onDelete('cascade');

            $table->foreignId('historia_id')
                  ->nullable()
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->foreignId('tratamiento_id')
                  ->nullable()
                  ->constrained('tratamientos')
                  ->onDelete('cascade');

            // Tipo de consentimiento
            $table->enum('tipo_consentimiento', [
                'tratamiento_general',
                'intervencion_quirurgica',
                'anestesia',
                'cirugia',
                'procedimiento_diagnostico',
                'extraccion_organos',
                'necropsia',
                'retiro_menor',
                'exoneracion_aborto',
                'abandono_hospital'
            ]);

            // INFORMACIÓN DEL TRATAMIENTO/PROCEDIMIENTO
            $table->text('propositos')->nullable()->comment('Propósitos del tratamiento');
            $table->text('procedimientos_propuestos')->nullable();
            $table->text('resultados_esperados')->nullable();
            $table->text('riesgos_complicaciones')->nullable();

            // INFORMACIÓN QUIRÚRGICA (si aplica)
            $table->text('intervenciones_quirurgicas')->nullable();
            $table->text('riesgos_quirurgicos')->nullable();

            // INFORMACIÓN ANESTÉSICA (si aplica)
            $table->string('anestesia_propuesta')->nullable();
            $table->text('riesgos_anestesicos')->nullable();
            $table->string('nombre_anestesiologo')->nullable();

            // CONSENTIMIENTO DEL PACIENTE
            $table->boolean('informacion_satisfactoria')->default(false);
            $table->boolean('actividades_explicadas')->default(false);
            $table->boolean('beneficios_riesgos_comprendidos')->default(false);
            $table->boolean('garantia_calidad_comprendida')->default(false);
            $table->boolean('respeto_intimidad_garantizado')->default(false);
            $table->boolean('derecho_anular_comprendido')->default(false);
            $table->boolean('informacion_completa_entregada')->default(false);

            // FIRMAS
            $table->string('firma_paciente')->nullable()->comment('Ruta a firma digital');
            $table->string('cedula_paciente', 10);
            $table->timestamp('fecha_firma_paciente')->nullable();

            // Representante legal (si aplica)
            $table->string('nombre_representante')->nullable();
            $table->string('cedula_representante', 10)->nullable();
            $table->string('parentesco_representante')->nullable();
            $table->string('firma_representante')->nullable();
            $table->timestamp('fecha_firma_representante')->nullable();

            // Testigo (si aplica)
            $table->string('nombre_testigo')->nullable();
            $table->string('cedula_testigo', 10)->nullable();
            $table->string('firma_testigo')->nullable();

            // Profesional tratante
            $table->foreignId('profesional_id')
                  ->constrained('usuarios')
                  ->onDelete('cascade');
            $table->string('firma_profesional')->nullable();
            $table->timestamp('fecha_firma_profesional')->nullable();

            // Documento generado
            $table->string('documento_pdf')->nullable()->comment('Ruta al PDF firmado');

            // Estado
            $table->enum('estado', ['pendiente', 'firmado', 'rechazado', 'anulado'])->default('pendiente');

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
        Schema::dropIfExists('consentimientos_informados');
    }
};
