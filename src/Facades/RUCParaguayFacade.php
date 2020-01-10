<?php 
namespace pabloacastillo\RUCParaguay\Facades;

use Illuminate\Support\Facades\Facade;

class RUCParaguayFacade extends Facade{

	protected static function getFacadeAccessor()
    {
    	return 'RUCParaguay';
        // return RUCParaguay::class;
    }
}