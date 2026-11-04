<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hc_examen_estomatognatico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')
                  ->unique()
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            // Bloque G: Examen del Sistema Estomatognático
            $table->boolean('sin_patologia')->default(true);
            // Regiones 1..12: Labios, Mejillas, Maxilar Sup, Maxilar Inf, Lengua, Paladar, Piso, Carrillos, Glándulas Salivales, Orofaringe, ATM, Ganglios
            $table->jsonb('hallazgos')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hc_examen_estomatognatico');
    }
};

