<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agentes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_osinergmin', 30)->unique();
            $table->string('codigo_actividad', 10)->nullable()->index();
            $table->string('actividad')->nullable();
            $table->string('nro_registro', 60)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->string('ruc', 20)->nullable()->index();
            $table->string('razon_social')->index();
            $table->text('direccion')->nullable();
            $table->string('ubigeo')->nullable();
            $table->string('placa', 30)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }
};
