<?php

namespace pabloacastillo\RUCParaguay\Console\Commands;

use Illuminate\Console\Command;

class RucParaguayCmdUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruc:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los datos de RUC';

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
        echo "\n========== STARTING ========== \n";
        echo "========== DOWNLOADING ========== \n";
        app('pabloacastillo\RUCParaguay\Http\Controllers\RucpyController')->Download();
        echo "\n========== FINISHED ========== \n\n";
    }
}
