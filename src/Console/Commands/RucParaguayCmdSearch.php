<?php

namespace pabloacastillo\RUCParaguay\Console\Commands;

use Illuminate\Console\Command;

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
            $busqueda = implode(", ",$busqueda);
        }
        echo "\n \t BUSCAR $busqueda \n\n";
    }
}
