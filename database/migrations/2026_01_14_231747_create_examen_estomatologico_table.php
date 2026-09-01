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
        Schema::create('examen_estomatologico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')->unique()->constrained('historias_clinicas')->onDelete('cascade');
            
            $table->text('labios')->nullable();
            $table->text('lengua')->nullable();
            $table->text('paladar')->nullable();
            $table->text('piso_boca')->nullable();
            $table->text('encias')->nullable();
            $table->text('carrillos')->nullable();
            $table->text('orofaringe')->nullable();
            $table->text('atm')->nullable();
            $table->text('glandulas_salivales')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examen_estomatologico');
    }
};
