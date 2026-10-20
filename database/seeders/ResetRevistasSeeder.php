<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResetRevistasSeeder extends Seeder
{
    public function run()
    {
        // Desactivar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Limpiar tablas
        DB::table('paginas')->truncate();
        DB::table('revistas')->truncate();
        DB::table('favoritos')->truncate();
        
        // Reactivar verificación
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Tablas de revistas limpiadas exitosamente');
    }
}