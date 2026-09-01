<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')->constrained('planes_tratamiento')->onDelete('cascade');
            $table->foreignId('procedimiento_id')->nullable()->constrained('cat_procedimientos')->nullOnDelete();
            $table->foreignId('profesional_id')->nullable()->constrained('usuarios')->nullOnDelete();
            
            $table->smallInteger('pieza_dental')->nullable();
            $table->string('cara_afectada', 20)->nullable();
            $table->date('fecha_realizado')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->decimal('costo_final', 10, 2)->nullable();
            $table->text('prescripcion')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('firma_profesional', 255)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};
