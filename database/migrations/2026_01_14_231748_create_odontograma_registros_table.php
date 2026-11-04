<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontograma_registros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historia_id')
                  ->constrained('historias_clinicas')
                  ->onDelete('cascade');

            $table->foreignId('profesional_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();

            // Bloque H y K: Pieza 11..48 (permanente) o 51..85 (temporal)
            $table->smallInteger('pieza');
            $table->string('denticion', 15)->default('permanente'); // permanente | temporal
            $table->string('superficie', 20)->nullable(); // O, M, D, V, L/P o null (diente completo)
            $table->string('categoria', 50)->nullable();
            $table->string('hallazgo', 80); // caries, obturado, sellante_necesario, extraccion_indicada, etc.
            $table->string('tipo_registro', 20)->default('patologia'); // patologia (rojo) | realizado (azul)
            $table->string('color_hex', 15)->default('#dc3545');

            // Movilidad (1..3) y Recesión (1..4) solo en permanentes
            $table->smallInteger('movilidad')->nullable();
            $table->smallInteger('recesion')->nullable();

            $table->text('observaciones')->nullable();

            // Inmutabilidad: para anular un clic erróneo sin hacer DELETE físico
            $table->foreignId('anulado_por_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->nullOnDelete();
            $table->timestamp('anulado_at')->nullable();
            $table->string('origen', 20)->default('manual'); // manual | ia

            $table->timestamps();

            $table->index(['historia_id', 'pieza', 'anulado_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontograma_registros');
    }
};

