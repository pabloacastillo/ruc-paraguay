# ruc-paraguay

Descargar, buscar y brindar información sobre los números de RUC desde la fuente oficial de DNIT (ex SET) en Paraguay.

## Install

```bash
composer require martinpa13py/ruc-paraguay
php artisan migrate
```

Autodiscovery registra el service provider. Si hace falta, en `config/app.php`:

```php
martinpa13py\RUCParaguay\RUCParaguayServiceProvider::class
'RUCParaguay' => martinpa13py\RUCParaguay\Facades\RUCParaguayFacade::class
```

Publicar configuración (URL de DNIT, caché, tamaño de lote):

```bash
php artisan vendor:publish --tag=ruc-paraguay-config
```

Tras actualizar el paquete, correr migraciones: se achican tipos de columna, se quitan timestamps sin uso y se agregan índices `(nro_ruc, digito_verificador)` y `estado`.

## Artisan

```bash
# Descarga completa (respeta caché de 48h)
php artisan ruc:update

# Forzar descarga e importación completa
php artisan ruc:update --force

# Prueba rápida: solo ruc0.zip y 1000 registros
php artisan ruc:update --test

# Un archivo y un tope de filas
php artisan ruc:update --files=0 --limit=500

# Lotes más grandes
php artisan ruc:update --batch-size=5000
```

`--limit` y `--test` solo descargan/extraen los ZIP indicados (por defecto `ruc0`). Si la tabla ya tiene datos, hay que pasar `--force` para no vaciar producción por accidente.

```bash
php artisan ruc:search Castillo Pablo
php artisan ruc:search 460018
```

La búsqueda por RUC numérico usa igualdad o prefijo (aprovecha el índice). Por nombre, `LIKE`. Máximo 50 filas salvo que se pase otro límite en código.

## Uso

```php
RUCParaguay::search([
    'nro_ruc' => '4600',
    'denominacion' => 'alejandro',
]);

RUCParaguay::search('460018');
RUCParaguay::search('460018-3');
RUCParaguay::search('CASTILLO', 20);
```

Programar `php artisan ruc:update` cada dos días (el caché local evita redescargar ZIP con menos de 48 h).

## Tests

```bash
composer test
```
