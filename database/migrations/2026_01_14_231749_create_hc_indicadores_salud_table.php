<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hc_indicadores_salud', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_id')
                  ->unique()
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->foreignId('profesional_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            // Bloque I: Higiene Oral Simplificada (JSONB con piezas 16/17/55, 11/21/51, 26/27/65, 36/37/75, 31/41/71, 46/47/85)
            $table->jsonb('higiene_oral')->nullable();
            $table->decimal('placa_total', 5, 2)->default(0);
            $table->decimal('calculo_total', 5, 2)->default(0);
            $table->decimal('gingivitis_total', 5, 2)->default(0);

            // Enfermedad Periodontal, Maloclusión, Fluorosis
            $table->string('enfermedad_periodontal', 20)->nullable(); // leve, moderada, severa, sana
            $table->string('maloclusion', 20)->nullable(); // angle_I, angle_II, angle_III, normal
            $table->string('fluorosis', 20)->nullable(); // leve, moderada, severa, normal

            // Bloque J: Índices CPO-ceo
            $table->smallInteger('cpo_c')->default(0);
            $table->smallInteger('cpo_p')->default(0);
            $table->smallInteger('cpo_o')->default(0);
            $table->smallInteger('cpo_total')->default(0);

            $table->smallInteger('ceo_c')->default(0);
            $table->smallInteger('ceo_e')->default(0);
            $table->smallInteger('ceo_o')->default(0);
            $table->smallInteger('ceo_total')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hc_indicadores_salud');
    }
};

