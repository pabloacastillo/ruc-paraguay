<?php

namespace martinpa13py\RUCParaguay\Models;

use Illuminate\Database\Eloquent\Model;

class RucParaguaySet extends Model
{
    public $timestamps = false;

    protected $table = 'ruc_paraguay_set';

    protected $fillable = [
        'nro_ruc',
        'denominacion',
        'digito_verificador',
        'ruc_anterior',
        'estado',
    ];
}
