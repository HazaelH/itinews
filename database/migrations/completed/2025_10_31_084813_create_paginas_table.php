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
    Schema::create('paginas', function (Blueprint $table) {
        $table->id();
        
        // Conectamos 'pagina' con 'revista'
        $table->foreignId('revista_id')
              ->constrained('revistas') // importante: especificar la tabla en español
              ->onDelete('cascade');

        $table->integer('numero_pagina');
        $table->string('ruta_imagen'); // La ruta a la imagen de esta página
        $table->timestamps();
    });
}
};
