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
        if (Schema::hasTable('fondos')) {
            return;
        }

        Schema::create('fondos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');

            // Origen de la imagen: archivo subido o portada de una revista.
            $table->string('origen')->default('archivo');
            $table->string('archivo')->nullable();
            $table->foreignId('revista_id')->nullable()->constrained('revistas')->nullOnDelete();

            // Dónde se muestra: inicio | revistas | ambos.
            $table->string('ubicacion')->default('ambos');

            // Vigencia: siempre | rango | conmemorativa.
            $table->string('tipo_vigencia')->default('siempre');
            $table->boolean('recurrente')->default(true);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['activo', 'ubicacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fondos');
    }
};
