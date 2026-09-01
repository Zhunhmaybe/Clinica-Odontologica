<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontogramas', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('historia_id')->constrained('historias_clinicas')->onDelete('cascade');
            $table->foreignId('profesional_id')->constrained('usuarios')->restrictOnDelete();

            $table->smallInteger('numero_pieza'); 
            $table->string('tipo_denticion', 15)->default('permanente'); 
            $table->string('cara_afectada', 20)->nullable();
            $table->string('estado', 30)->default('sano'); 

            $table->boolean('necesita_sellante')->default(false);
            $table->smallInteger('movilidad')->nullable(); 
            $table->smallInteger('recesion')->nullable();  
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogramas');
    }
};
