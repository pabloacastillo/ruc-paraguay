<?php

namespace martinpa13py\RUCParaguay\Console\Commands;

use Illuminate\Console\Command;
use martinpa13py\RUCParaguay\Services\RUCParaguayUpdater;

class RucParaguayCmdUpdate extends Command
{
    protected $signature = 'ruc:update
                            {--force : Forzar descarga y reemplazo de datos existentes}
                            {--batch-size=2000 : Cantidad de registros a insertar por lote (mínimo 100)}
                            {--limit= : Límite máximo de registros a importar (útil para pruebas)}
                            {--test : Muestra rápida: solo ruc0.zip y 1000 registros}
                            {--files= : Índices de ZIP a procesar, separados por coma (ej. 0 o 0,1)}';

    protected $description = 'Actualiza los datos de RUC de Paraguay descargando e importando desde DNIT';

    public function handle(): int
    {
        $this->info('========== INICIANDO ==========');

        $updater = app(RUCParaguayUpdater::class);
        $updater->setOutput(fn (string $msg) => $this->line($msg));

        if ($this->option('force')) {
            $updater->setForce(true);
        }

        $updater->setBatchSize((int) $this->option('batch-size'));

        if ($this->option('test')) {
            $updater->setTest(true);
            $this->info('Modo test: archivo ruc0, límite 1000 (salvo --limit)');
        }

        if ($this->option('files') !== null && $this->option('files') !== '') {
            $indexes = array_map('intval', explode(',', (string) $this->option('files')));
            $updater->setFiles($indexes);
            $this->info('Archivos: ' . implode(', ', $indexes));
        }

        if ($this->option('limit')) {
            $limit = (int) $this->option('limit');
            if ($limit > 0) {
                $updater->setLimit($limit);
                $this->info("Límite de importación: {$limit} registros");
            }
        }

        $updater->run();

        $this->info('========== FINALIZADO ==========');

        return Command::SUCCESS;
    }
}
