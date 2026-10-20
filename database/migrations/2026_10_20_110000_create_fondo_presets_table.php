<?php

use App\Models\FondoPreset;
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
        if (! Schema::hasTable('fondo_presets')) {
            Schema::create('fondo_presets', function (Blueprint $table) {
                $table->id();
                $table->string('tipo'); // conmemorativa | rango
                $table->string('nombre');
                $table->string('dia', 10)->nullable();   // MM-DD
                $table->string('desde', 10)->nullable(); // MM-DD
                $table->string('hasta', 10)->nullable(); // MM-DD
                $table->unsignedInteger('orden')->default(0);
                $table->timestamps();

                $table->index(['tipo', 'orden']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fondo_presets');
    }
};
