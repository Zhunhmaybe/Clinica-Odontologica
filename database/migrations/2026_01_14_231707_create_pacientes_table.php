<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 10)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('email')->nullable();
            $table->string('telefono', 10);
            $table->date('fecha_nacimiento');
            $table->text('direccion')->nullable();

            $table->boolean('consentimiento_lopdp')->default(false);
            $table->timestamp('fecha_firma_lopdp')->nullable();
            $table->jsonb('datos_dinamicos')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pacientes');
    }
};
