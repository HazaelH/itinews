<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('revistas', function (Blueprint $table) {
            $table->enum('estado_procesamiento', ['pendiente', 'procesando', 'completado', 'error'])
                  ->default('pendiente')
                  ->after('ruta_pdf');
        });
    }

    public function down()
    {
        Schema::table('revistas', function (Blueprint $table) {
            $table->dropColumn('estado_procesamiento');
        });
    }
};