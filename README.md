# ruc-paraguay
Fetch, search and retrieve RUC codes from the official taxes database from SET. 

Descargar, buscar y brindar información sobre los numeros de RUC desde la fuente oficial del SET en Paraguay.


## Install

Use composer to install the package, just run this comamnd to add it to the composer.json

`composer require pabloacastillo/ruc-paraguay`


The autodiscovery option should take care of things, but if that doesnt work follow the next steps.

Add the package to config/app.php to the providers list:

`pabloacastillo\RUCParaguay\RUCParaguayServiceProvider::class`

Add the package to config/app.php to the aliases list:

`'RUCParaguay' => pabloacastillo\RUCParaguay\Facades\RUCParaguayFacade::class`




## Artisan Commands 

This are the artisan commands avaible, you should hook update the data at least once a month.

`php artisan ruc:update`

Fetchs and updates all the local data from the source. Will download several zip files, decompress to text files and load them into the database.

`php artisan ruc:search Castillo Pablo`

Performs a searchs inside the database with the provided information. Will return  results and some debug info.

## HOW TO USE

To search for information inside the database you can especify the fields you are looking for like this:
```
$toSearch=array(
	'nro_ruc' 		=>'4600',
	'denominacion' 	=>'alejandro',
	'ruc_anterior' 	=>'ca',
);
RUCParaguay::search($toSearch);
```

You can also search with only one field:

```
$toSearch=array(
	'nro_ruc' 		=>'460018',
);
RUCParaguay::search($toSearch);
```

Or you can search the whole thing like this:

```
RUCParaguay::search('46001');
```

To update the database periodically the update command should be hooked to a cron command to run every couple of days. The update is hardset inside the code to be able to be executed a maximun of once every 48 hours.

`php artisan ruc:update`


### TODO

- [X] Add code examples.

- [ ] Find a way to make it faster without dumping direct-to-database.