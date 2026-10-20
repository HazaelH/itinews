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
        Schema::create('analytics_page_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visit_id')->constrained('analytics_visits')->cascadeOnDelete();
            $table->foreignId('revista_id')->nullable()->constrained('revistas')->nullOnDelete();
            $table->unsignedInteger('page_number');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();

            $table->index(['revista_id', 'page_number']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_page_events');
    }
};
