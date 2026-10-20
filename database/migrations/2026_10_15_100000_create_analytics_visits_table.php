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
        Schema::create('analytics_visits', function (Blueprint $table) {
            $table->id();

            // Identificadores anónimos
            $table->uuid('visit_token')->unique();       // una sesión de lectura
            $table->uuid('visitor_token')->index();      // visitante recurrente (cookie/localStorage)

            // Contexto de la visita
            $table->string('tipo', 20)->default('revista')->index(); // portal | revista
            $table->foreignId('revista_id')->nullable()->constrained('revistas')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // Dispositivo (detectado en el servidor)
            $table->string('device_type', 20)->default('unknown')->index(); // desktop|mobile|tablet|bot|unknown
            $table->string('os', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('browser_version', 30)->nullable();
            $table->boolean('is_bot')->default(false)->index();

            // Contexto de red / navegación (sin datos personales en claro)
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('user_agent', 512)->nullable();
            $table->string('referrer', 512)->nullable();
            $table->string('path', 255)->nullable();

            // Métricas de lectura
            $table->unsignedInteger('pages_viewed')->default(0);
            $table->unsignedInteger('total_pages')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);

            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_visits');
    }
};
