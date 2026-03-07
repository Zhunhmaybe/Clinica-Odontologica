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
        Schema::create('campos_formularios', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_formulario', 50);
            $table->string('etiqueta', 255);
            $table->string('tipo_dato', 50);
            $table->boolean('es_requerido')->default(false);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
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
        Schema::dropIfExists('campos_formularios');
    }
};
