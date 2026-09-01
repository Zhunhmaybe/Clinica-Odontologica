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
        Schema::create('indices_salud_bucal', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('historia_id')->unique()->constrained('historias_clinicas')->onDelete('cascade');
            $table->foreignId('profesional_id')->constrained('usuarios')->restrictOnDelete();

            $table->smallInteger('cpo_cariados')->default(0);
            $table->smallInteger('cpo_perdidos')->default(0);
            $table->smallInteger('cpo_obturados')->default(0);

            $table->smallInteger('ceo_cariados')->default(0);
            $table->smallInteger('ceo_extraccion')->default(0);
            $table->smallInteger('ceo_obturados')->default(0);

            $table->smallInteger('placa_bacteriana')->default(0);
            $table->smallInteger('calculo_dental')->default(0);
            $table->smallInteger('gingivitis')->default(0);
            
            $table->string('nivel_fluorosis', 50)->nullable();
            $table->string('tipo_oclusion', 50)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indices_salud_bucal');
    }
};
