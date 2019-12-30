<?php

namespace pabloacastillo\RUCParaguay\Console\Commands;

use Illuminate\Console\Command;
use pabloacastillo\RUCParaguay\Models\RucParaguaySet;


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
        $busqueda = $this->argument('busqueda');
        if(is_array($busqueda)){
            $_busqueda = implode(" ",$busqueda);
            $busqueda = implode("%",$busqueda);
        }

        $records=RucParaguaySet::where('nro_ruc','LIKE',"%{$busqueda}%")->orWhere('denominacion','LIKE',"%{$busqueda}%")->orWhere('ruc_anterior','LIKE',"%{$busqueda}%")->get();
        echo "\n \t BUSCAR $_busqueda \n\n";
        foreach ($records as $record) {
            $str='RUC: '.$record['nro_ruc'].'-'.$record['digito_verificador'].' ('.$record['denominacion'].') '.$record['ruc_anterior'];
            $str=utf8_decode($str);
            echo "$str \n";
        }
    }
}
