<?php 

namespace pabloacastillo\RUCParaguay;
use Illuminate\Support\ServiceProvider;
use Storage;
use GuzzleHttp\Client;


class RUCParaguayServiceProvider extends ServiceProvider{


	public function boot(){
		$this->commands([
			\pabloacastillo\RUCParaguay\Console\Commands\RucParaguayCmdUpdate::class,
			\pabloacastillo\RUCParaguay\Console\Commands\RucParaguayCmdSearch::class,
		]);

		$this->loadMigrationsFrom(__DIR__.'/database/migrations/');
	}

	public function register(){

	}



}