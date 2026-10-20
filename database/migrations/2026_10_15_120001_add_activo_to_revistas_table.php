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
        if (! Schema::hasTable('revistas') || Schema::hasColumn('revistas', 'activo')) {
            return;
        }

        Schema::table('revistas', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('total_paginas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('revistas') || ! Schema::hasColumn('revistas', 'activo')) {
            return;
        }

        Schema::table('revistas', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
