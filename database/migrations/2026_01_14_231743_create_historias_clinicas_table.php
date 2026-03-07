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

            // Relación
            $table->foreignId('paciente_id')
                  ->constrained('pacientes')
                  ->onDelete('cascade');

            // Identificación
            $table->string('numero_historia', 50)->nullable();
            $table->date('fecha_atencion');
            $table->string('estado_historia', 20)->default('abierta');

            // Consulta
            $table->text('motivo_consulta')->nullable();
            $table->text('enfermedad_actual')->nullable();

            // Antecedentes personales
            $table->text('alergias')->nullable();
            $table->boolean('cardiopatias')->default(false);
            $table->boolean('diabetes')->default(false);
            $table->boolean('hipertension')->default(false);
            $table->boolean('tuberculosis')->default(false);
            $table->text('antecedentes_otros')->nullable();

            // Antecedentes familiares
            $table->boolean('fam_diabetes')->default(false);
            $table->boolean('fam_hipertension')->default(false);
            $table->boolean('fam_cancer')->default(false);
            $table->boolean('fam_tuberculosis')->default(false);

            // Constantes vitales
            $table->decimal('temperatura', 4, 2)->nullable();
            $table->string('presion_arterial', 20)->nullable();
            $table->integer('pulso')->nullable();
            $table->integer('frecuencia_respiratoria')->nullable();

            // Examen clínico general
            $table->text('labios')->nullable();
            $table->text('lengua')->nullable();
            $table->text('paladar')->nullable();
            $table->text('piso_boca')->nullable();
            $table->text('encias')->nullable();
            $table->text('carrillos')->nullable();
            $table->text('orofaringe')->nullable();
            $table->text('atm')->nullable();
            $table->json('patologias_personales')->nullable()->after('tuberculosis');
            $table->json('alergias_lista')->nullable()->after('alergias');
            $table->json('patologias_familiares')->nullable()->after('fam_tuberculosis');

            // Observaciones
            $table->text('observaciones')->nullable();

            // Auditoría
            $table->unsignedBigInteger('profesional_id')->nullable();
            $table->timestamps();
        });
    }

public function down(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->dropColumn(['patologias_personales', 'alergias_lista', 'patologias_familiares']);
        });
    }
};
