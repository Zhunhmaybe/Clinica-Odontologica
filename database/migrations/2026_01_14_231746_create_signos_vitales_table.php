<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('signos_vitales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')->unique()->constrained('historias_clinicas')->onDelete('cascade');
            
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->smallInteger('presion_sistolica')->nullable();
            $table->smallInteger('presion_diastolica')->nullable();
            $table->smallInteger('pulso')->nullable();
            $table->smallInteger('frecuencia_respiratoria')->nullable();
            $table->smallInteger('saturacion_oxigeno')->nullable();
            $table->decimal('peso', 5, 2)->nullable();
            $table->decimal('talla', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signos_vitales');
    }
};
