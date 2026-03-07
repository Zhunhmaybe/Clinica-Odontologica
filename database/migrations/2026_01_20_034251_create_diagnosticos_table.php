<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosticos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->text('descripcion');
            $table->enum('tipo', ['presuntivo', 'definitivo'])->default('presuntivo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosticos');
    }
};
