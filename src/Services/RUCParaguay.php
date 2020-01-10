<?php 
namespace pabloacastillo\RUCParaguay\Services;
use pabloacastillo\RUCParaguay\Models\RucParaguaySet;


class RUCParaguay{

	public static function search($busqueda){


		$records=RucParaguaySet::where('id','>',"0");
		if(isset($busqueda['nro_ruc'])){
        	$records=$records->where('nro_ruc','LIKE',"%{$busqueda['nro_ruc']}%");
        	unset($busqueda['nro_ruc']);
		}

		if(isset($busqueda['denominacion'])){
        	$records=$records->where('denominacion','LIKE',"%{$busqueda['denominacion']}%");
        	unset($busqueda['denominacion']);
		}

		if(isset($busqueda['ruc_anterior'])){
        	$records=$records->where('ruc_anterior','LIKE',"%{$busqueda['ruc_anterior']}%");
        	unset($busqueda['ruc_anterior']);
		}

		if( (!empty($busqueda)) && (is_array($busqueda)) ){
            $busqueda = implode("%",$busqueda);
		}

		if(is_string($busqueda)){
        	$records=RucParaguaySet::where('nro_ruc','LIKE',"%{$busqueda}%")->orWhere('denominacion','LIKE',"%{$busqueda}%")->orWhere('ruc_anterior','LIKE',"%{$busqueda}%");
		}


		return $records->get();
	}
}