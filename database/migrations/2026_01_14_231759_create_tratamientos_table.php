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

            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->date('fecha')->nullable();
            $table->text('procedimiento')->nullable();
            $table->text('prescripcion')->nullable();
            $table->string('firma_profesional', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};
