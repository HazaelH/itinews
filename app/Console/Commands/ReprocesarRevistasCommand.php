<?php

namespace App\Console\Commands;

use App\Jobs\ProcesarPdfJob;
use App\Models\Revista;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ReprocesarRevistasCommand extends Command
{
    protected $signature = 'revistas:reprocesar {id? : ID específico de la revista a reprocesar} {--all : Reprocesar todas las revistas activas}';
    protected $description = 'Reprocesa una o todas las revistas con el nuevo motor Poppler a alta resolución (180 DPI WebP)';

    public function handle()
    {
        $id = $this->argument('id');
        $all = $this->option('all');

        if (!$id && !$all) {
            $this->error('Debes especificar un ID de revista o usar --all.');
            return 1;
        }

        $query = Revista::query();
        if ($id) {
            $query->where('id', $id);
        }

        $revistas = $query->get();

        if ($revistas->isEmpty()) {
            $this->warn('No se encontraron revistas para procesar.');
            return 0;
        }

        foreach ($revistas as $revista) {
            $this->info("Iniciando reprocesamiento para Revista #{$revista->id} - {$revista->titulo}...");
            
            if (!$revista->ruta_pdf) {
                $this->warn("Revista #{$revista->id} no tiene ruta_pdf asociada.");
                continue;
            }

            $pdfPath = storage_path('app/public/' . $revista->ruta_pdf);
            if (!file_exists($pdfPath)) {
                $this->error("El archivo PDF no existe en: {$pdfPath}");
                continue;
            }

            $nombreCarpeta = Str::slug($revista->titulo) . '-' . time();
            
            try {
                $job = new ProcesarPdfJob($revista, $nombreCarpeta);
                $job->handle();
                $this->info("✓ Revista #{$revista->id} procesada exitosamente con calidad Ultra HD.");
            } catch (\Exception $e) {
                $this->error("Error procesando Revista #{$revista->id}: " . $e->getMessage());
            }
        }

        $this->info('Reprocesamiento finalizado.');
        return 0;
    }
}
