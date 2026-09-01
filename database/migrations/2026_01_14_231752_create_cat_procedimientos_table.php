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
        Schema::create('cat_procedimientos', function (Blueprint $table) {
            $table->id();
            
            $table->string('codigo', 20)->unique()->nullable();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->string('categoria', 50)->nullable();
            $table->decimal('costo_base', 10, 2)->default(0.00);
            $table->smallInteger('duracion_min')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_procedimientos');
    }
};
