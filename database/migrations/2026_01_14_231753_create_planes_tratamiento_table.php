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
        Schema::create('planes_tratamiento', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('historia_id')->constrained('historias_clinicas')->onDelete('cascade');
            $table->foreignId('profesional_id')->constrained('usuarios')->restrictOnDelete();
            
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('propuesto');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('costo_total', 10, 2)->default(0.00);
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_tratamiento');
    }
};
