# ruc-paraguay
Fetch, search and retrieve RUC codes from the official taxes database from SET. 

Descargar, buscar y brindar información sobre los numeros de RUC desde la fuente oficial del SET en Paraguay.




Commands 

`php artisan ruc:update`

Fetchs and updates all the local data from the source. Will download several zip files, decompress to text files and load them into the database.

`php artisan ruc:search Castillo Pablo`

Performs a searchs inside the database with the provided information. Will return  results and some debug info.

## HOW TO USE

To update the database periodically the update command should be hooked to a cron command to run every couple of days. The update is hardset inside the code to be able to be executed a maximun of once every 48 hours.

### TODO

- [ ] Add code examples.

- [ ] Find a way to make it faster without dumping direct-to-database.