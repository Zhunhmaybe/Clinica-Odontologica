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
        Schema::create('antecedentes_familiares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('historia_id')->unique()->constrained('historias_clinicas')->onDelete('cascade');
            
            $table->boolean('diabetes')->default(false);
            $table->boolean('hipertension')->default(false);
            $table->boolean('cancer')->default(false);
            $table->boolean('tuberculosis')->default(false);
            $table->boolean('cardiopatias')->default(false);
            $table->boolean('enf_mentales')->default(false);
            $table->text('otros')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antecedentes_familiares');
    }
};
