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
        if (!Schema::hasTable('ruc_paraguay_set')) {

            Schema::create('ruc_paraguay_set', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nro_ruc',32)->nullable();
                $table->string('denominacion',512)->nullable();
                $table->string('digito_verificador',8)->nullable();
                $table->string('ruc_anterior',32)->nullable();
                $table->string('estado',40)->nullable();
                $table->timestamps();
            });
            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
