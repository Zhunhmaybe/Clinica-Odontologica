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
        Schema::create('antecedentes_personales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')->unique()->constrained('historias_clinicas')->onDelete('cascade');
            
            $table->text('alergias')->nullable();
            $table->boolean('cardiopatias')->default(false);
            $table->boolean('diabetes')->default(false);
            $table->boolean('hipertension')->default(false);
            $table->boolean('tuberculosis')->default(false);
            $table->boolean('asma')->default(false);
            $table->boolean('hepatitis')->default(false);
            $table->boolean('vih_sida')->default(false);
            $table->boolean('epilepsia')->default(false);
            $table->boolean('embarazo')->default(false);
            $table->text('otros')->nullable();
            $table->text('medicamentos_actuales')->nullable();
            $table->text('cirugias_previas')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antecedentes_personales');
    }
};
