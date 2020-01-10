<?php

namespace pabloacastillo\RUCParaguay\Console\Commands;

use Illuminate\Console\Command;
use pabloacastillo\RUCParaguay\Models\RucParaguaySet;
use pabloacastillo\RUCParaguay\Services\RUCParaguay;


class RucParaguayCmdSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruc:search {busqueda*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buscar RUC ruc:search 123456';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $start = time();
        $busqueda = $this->argument('busqueda');
        $_busqueda = implode(" ",$busqueda);

        $records=RUCParaguay::search($busqueda);
        foreach ($records as $record) {
            $str='RUC: '.$record['nro_ruc'].'-'.$record['digito_verificador'].' ('.$record['denominacion'].') - OLD RUC: '.$record['ruc_anterior'];
            $str=utf8_decode($str);
            echo "$str \n";
        }

        echo "\n-> Finished in ".(time()-$start)." seconds.\n";
    }
}
