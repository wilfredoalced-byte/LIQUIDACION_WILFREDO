<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codigos_unidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('orden')->default(0);
            $table->string('codigo', 10)->index();
            $table->text('descripcion');
            $table->string('accion')->nullable();
            $table->string('asignacion')->nullable();
            $table->string('plazo_general_con_visita', 10)->nullable();
            $table->string('plazo_general_sin_visita', 10)->nullable();
            $table->string('plazo_cl_glp_con_visita', 10)->nullable();
            $table->string('plazo_cl_glp_sin_visita', 10)->nullable();
            $table->string('plazo_gnv_con_visita', 10)->nullable();
            $table->string('plazo_gnv_sin_visita', 10)->nullable();
            $table->string('plazo_mayores_con_visita', 10)->nullable();
            $table->string('plazo_mayores_sin_visita', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_unidad');
    }
};
