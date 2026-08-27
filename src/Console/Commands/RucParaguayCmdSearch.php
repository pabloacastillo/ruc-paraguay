<?php

namespace martinpa13py\RUCParaguay\Console\Commands;

use Illuminate\Console\Command;
use martinpa13py\RUCParaguay\Services\RUCParaguay;

class RucParaguayCmdSearch extends Command
{
    protected $signature = 'ruc:search {busqueda*}';

    protected $description = 'Buscar RUC ruc:search 123456';

    public function handle(): int
    {
        $start = time();
        $busqueda = implode(' ', $this->argument('busqueda'));

        $records = RUCParaguay::search($busqueda);
        foreach ($records as $record) {
            $this->line(
                'RUC: ' . $record['nro_ruc'] . '-' . $record['digito_verificador']
                . ' (' . $record['denominacion'] . ') - OLD RUC: ' . ($record['ruc_anterior'] ?? '')
            );
        }

        $this->line('');
        $this->line('-> Finished in ' . (time() - $start) . ' seconds.');

        return Command::SUCCESS;
    }
}
