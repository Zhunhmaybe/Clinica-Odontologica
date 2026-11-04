<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo oficial CIE-10 Odontológico (K00 - K14)
        Schema::create('cie10', function (Blueprint $table) {
            $table->string('codigo', 10)->primary();
            $table->string('nombre', 255);
            $table->string('categoria', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Bloque N: Diagnósticos de la Historia Clínica
        Schema::create('hc_diagnosticos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->string('cie10_codigo', 10)->nullable();
            $table->foreign('cie10_codigo')->references('codigo')->on('cie10')->nullOnDelete();

            $table->text('descripcion');
            $table->string('tipo', 15)->default('PRE'); // PRE (Presuntivo) | DEF (Definitivo)
            $table->smallInteger('orden')->default(1);
            $table->smallInteger('pieza_dental')->nullable();
            $table->string('origen', 20)->default('manual'); // manual | ia

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hc_diagnosticos');
        Schema::dropIfExists('cie10');
    }
};

