<?php

namespace pabloacastillo\RUCParaguay\Http\Controllers;

use App\Http\Controllers\Controller;

use Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ZipArchive;

use pabloacastillo\RUCParaguay\Models\RucParaguaySet;

class RucpyController extends Controller
{
    //
	protected $url='http://www.set.gov.py/rest/contents/download/collaboration/sites/PARAGUAY-SET/documents/informes-periodicos/ruc/';

	public function search($busqueda){
		$records=RucParaguaySet::where('nro_ruc','LIKE',"%{$busqueda}%")->orWhere('denominacion','LIKE',"%{$busqueda}%")->orWhere('ruc_anterior','LIKE',"%{$busqueda}%")->get();
		return $records;
	}

	public function add_new($data){
		if($data==false) { return false; }
		return RucParaguaySet::create($data);
	}

	public function Download(){
		$_START=time();
		$local_folder='ruc-paraguay';
		$storagePath = Storage::disk('local')->path($local_folder);
	 	$client = new \GuzzleHttp\Client();
	 	$now = (3600 * 24 * 2); // ACTUALIZAR CADA 2 DIAS
	 	$delimiter = '|';

	 	$NEW_DATA=false;

	 	if(!Storage::disk('local')->exists($local_folder) ){
	 		echo "\n-> CREATE DIRECTORY $storagePath \n";
	 		Storage::disk('local')->makeDirectory($local_folder, 0775, true);
	 	}
	 	echo "\n-> LOCAL FOLDER: $storagePath \n";

	 	for($i=0;$i<=9;$i++){
	 		$file_path='ruc'.$i.'.zip';
	 		$URL = ($this->url).$file_path;

	 		echo "\n-> URL: $URL";

	 		if( Storage::disk('local')->exists($local_folder.'/'.$file_path) ){
	 			if ( (time() - (Storage::disk('local')->lastModified($local_folder.'/'.$file_path))) < $now ) {
	 				echo "\n-> DOWNLOADED: ".$storagePath.'/'.$file_path." \n";
	 				continue;
	 			}
	 		}
	 		$NEW_DATA=true;
	 		echo "\n-> DOWNLOADING: ".$storagePath.'/'.$file_path." \n";
	 		$response = $client->request('GET',$URL, ['save_to' => $storagePath.'/'.$file_path]);
	 	}

	 	if($NEW_DATA==false) { 
	 		echo 'NO NEW DATA TO IMPORT';
	 		return ''; 
	 	}

	 	foreach (Storage::disk('local')->files($local_folder) as $ZIPPED_FILE) {
	 		if( strtolower(substr($ZIPPED_FILE, -4)) == '.zip' ){
	 			$local_zip = Storage::disk('local')->path($ZIPPED_FILE);
	 			echo "\n";
	 			echo $local_zip;
	 			echo "\n";
	 			$zip = new ZipArchive;
	 			if ($zip->open($local_zip) === TRUE){
	 				$zip->extractTo($storagePath);
	 				$zip->close();
	 			} else {
	 				Storage::disk('local')->delete($ZIPPED_FILE);
	 			}
	 		}
	 	}

	 	RucParaguaySet::truncate();

	 	foreach (Storage::disk('local')->files($local_folder) as $TXT_FILE) {
	 		if( strtolower(substr($TXT_FILE, -4)) == '.txt' ){
	 			$local_txt = Storage::disk('local')->path($TXT_FILE);
	 			echo "\nSTARTING IMPORT FROM $local_txt \n";
	 			$file = fopen($local_txt, "r");
				while(!feof($file)) {
					$line = fgets($file);
					if ( rand(0,10)<3 ){ echo($line); }
					$data=$this->txt2ruc($line);
					$record=$this->add_new($data);
				}

				fclose($file);
	 			echo "\nENDED IMPORT FROM $local_txt \n";
	 		}
	 	}

	 	echo "\n TERMINADO EN: ";
	 	echo ((time()-$_START));
	 	echo " segundos \n";
	}

	public function txt2ruc($line){
		$line = str_replace('||', '|', $line);
		$data=explode('|',$line);
	 	$_DEFAULT = array( 'nro_ruc'=>'----' , 'denominacion'=>'---' , 'digito_verificador'=>'-' , 'ruc_anterior'=>'--' );
	 	$_data=array();

	 	if(count($data)!=5) { return false; }

	 	$_data['nro_ruc']=$data[0];
	 	$_data['denominacion']=$data[1];
	 	$_data['digito_verificador']=$data[2];
	 	$_data['ruc_anterior']=$data[3];

		$_data = array_filter($_data,'trim');
		
		$_data = array_merge($_DEFAULT,$_data);

		return $_data;
	}

}

