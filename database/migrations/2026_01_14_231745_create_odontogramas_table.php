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
        Schema::create('odontogramas', function (Blueprint $table) {
            $table->id();
            // Relación con la historia clínica
            $table->foreignId('historia_id')->constrained('historias_clinicas')->onDelete('cascade');

            // Datos del diente
            $table->integer('numero_pieza'); // 18, 17, ...
            $table->string('tipo_denticion')->default('permanente'); // permanente/temporal
            $table->string('estado')->default('sano'); // sano, caries, obturado...

            // Detalles adicionales
            $table->boolean('necesita_sellante')->default(false);
            $table->integer('movilidad')->nullable(); // 1, 2, 3
            $table->integer('recesion')->nullable();  // 1, 2, 3
            $table->text('observaciones')->nullable();

            // Auditoría
            $table->foreignId('profesional_id')->constrained('usuarios');

            $table->timestamps();
        });

        // Tabla para índices de salud (CPO / ceo)
        Schema::create('indices_salud_bucal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')->constrained('historias_clinicas')->onDelete('cascade');

            // Índices CPO (Permanentes)
            $table->integer('cpo_cariados')->default(0);
            $table->integer('cpo_perdidos')->default(0);
            $table->integer('cpo_obturados')->default(0);

            // Índices ceo (Temporales)
            $table->integer('ceo_cariados')->default(0);
            $table->integer('ceo_extraccion')->default(0);
            $table->integer('ceo_obturados')->default(0);

            // Higiene
            $table->integer('placa_bacteriana')->default(0);
            $table->integer('calculo_dental')->default(0);
            $table->integer('gingivitis')->default(0);

            $table->string('nivel_fluorosis')->nullable();
            $table->string('tipo_oclusion')->nullable();

            $table->foreignId('profesional_id')->constrained('usuarios');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('indices_salud_bucal');
        Schema::dropIfExists('odontogramas');
    }
};
