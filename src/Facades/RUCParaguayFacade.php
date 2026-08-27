<?php 
namespace martinpa13py\RUCParaguay\Facades;

use Illuminate\Support\Facades\Facade;

class RUCParaguayFacade extends Facade{

	protected static function getFacadeAccessor()
    {
    	return 'RUCParaguay';
    }
}