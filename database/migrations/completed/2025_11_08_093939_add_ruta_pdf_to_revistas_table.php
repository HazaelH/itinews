<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revistas', function (Blueprint $table) {
            $table->string('ruta_pdf')->nullable()->after('ruta_portada');
        });
    }

    public function down(): void
    {
        Schema::table('revistas', function (Blueprint $table) {
            $table->dropColumn('ruta_pdf');
        });
    }
};
