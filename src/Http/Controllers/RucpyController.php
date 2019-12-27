<?php

namespace pabloacastillo\RUCParaguay\Http\Controllers;

use App\Http\Controllers\Controller;

use Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class RucpyController extends Controller
{
    //
	protected $url='http://www.set.gov.py/rest/contents/download/collaboration/sites/PARAGUAY-SET/documents/informes-periodicos/ruc/';


	public function Download(){
		$local_folder='ruc-paraguay';
		$storagePath = Storage::disk('local')->path($local_folder);
	 	$client = new \GuzzleHttp\Client();

	 	if(!Storage::disk('local')->exists($local_folder) ){
	 		echo "\n-> CREATE DIRECTORY $storagePath \n";
	 		Storage::disk('local')->makeDirectory($local_folder, 0775, true);
	 	}
	 	echo "\n-> LOCAL FOLDER: $storagePath \n";


	 	for($i=0;$i<=9;$i++){
	 		$file_path='ruc'.$i.'.zip';
	 		$URL = ($this->url).$file_path;
	 		$now = (3600 * 24 * 15); // ACTUALIZAR CADA 15 DIAS

	 		echo "\n-> URL: $URL";

	 		if( Storage::disk('local')->exists($local_folder.'/'.$file_path) ){
	 			if ( (time() - (Storage::disk('local')->lastModified($local_folder.'/'.$file_path))) < $now ) {
	 				echo "\n-> DOWNLOADED: ".$storagePath.'/'.$file_path." \n";
	 				continue;
	 			}
	 		}

	 		echo "\n-> DOWNLOADING: ".$storagePath.'/'.$file_path." \n";
	 		$response = $client->request('GET',$URL, ['save_to' => $storagePath.'/'.$file_path]);
	 	}


	 	// $zip = new ZipArchive;

	}
}
