<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRucParaguaySetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ruc_paraguay_set', function (Blueprint $table) {
            // 1000311|DELGADILLO DE VEGA, GUILLERMINA|8|GAGA820361S|
            $table->bigIncrements('id');
            $table->integer('nro_ruc');
            $table->string('denominacion',512);
            $table->integer('digito_verificador');
            $table->string('ruc_anterior',32);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ruc_paraguay_sets');
    }
}
