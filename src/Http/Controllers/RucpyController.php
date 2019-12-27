<?php

namespace pabloacastillo\RUCParaguay\Http\Controllers;

use App\Http\Controllers\Controller;

class RucpyController extends Controller
{
    //
	protected $url='http://www.set.gov.py/rest/contents/download/collaboration/sites/PARAGUAY-SET/documents/informes-periodicos/ruc/';




	
	public function Download(){
		$storagePath = Storage::disk('local')->path('ruc-paraguay');
	 	$client = new \GuzzleHttp\Client();

	 	for($i=0;$i<=9;$i++){
	 		$file_path='ruc'.$i.'.zip';
	 		$URL = ($this->url).$file_path;
	 		$client = new Client();
	 		$now = (3600 * 24 * 15); // ACTUALIZAR CADA 15 DIAS

	 		if( Storage::disk('local')->exists($storagePath.'/'.$file_path) ){
	 			if ( (time() - (Storage::lastModified($storagePath.'/'.$file_path))) < $now ) {
	 				continue;
	 			}
	 		}

	 		$response = $client->request('GET',$URL, ['save_to' => $storagePath.'/'.$file_path]);
	 	}

	}
}
