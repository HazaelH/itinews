<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('revistas', function (Blueprint $table) {
            $table->integer('progreso')->default(0)->after('estado_procesamiento');
            $table->integer('total_paginas')->default(0)->after('progreso');
        });
    }

    public function down()
    {
        Schema::table('revistas', function (Blueprint $table) {
            $table->dropColumn(['progreso', 'total_paginas']);
        });
    }
};