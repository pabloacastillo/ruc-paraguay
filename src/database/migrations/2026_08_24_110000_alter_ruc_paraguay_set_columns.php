<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterRucParaguaySetColumns extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ruc_paraguay_set')) {
            return;
        }

        if ($this->isSqlite()) {
            $this->rebuildSqliteTable();
            return;
        }

        DB::table('ruc_paraguay_set')->where(function ($q) {
            $q->whereNull('nro_ruc')->orWhere('nro_ruc', '');
        })->delete();
        DB::table('ruc_paraguay_set')->whereNull('denominacion')->update(['denominacion' => '']);
        DB::table('ruc_paraguay_set')->whereNull('estado')->update(['estado' => '']);
        DB::table('ruc_paraguay_set')->whereNull('digito_verificador')->update(['digito_verificador' => '']);

        Schema::table('ruc_paraguay_set', function (Blueprint $table) {
            if (Schema::hasColumn('ruc_paraguay_set', 'created_at')) {
                $table->dropColumn(['created_at', 'updated_at']);
            }
        });

        $mysql = $this->isMysql();

        Schema::table('ruc_paraguay_set', function (Blueprint $table) use ($mysql) {
            $nroRuc = $table->string('nro_ruc', 8)->nullable(false);
            $dv = $table->char('digito_verificador', 1)->nullable(false)->default('');
            $rucAnterior = $table->string('ruc_anterior', 16)->nullable();
            $estado = $table->string('estado', 20)->nullable(false)->default('');
            $table->string('denominacion', 255)->nullable(false)->change();

            if ($mysql) {
                $nroRuc->charset('ascii')->collation('ascii_bin');
                $dv->charset('ascii')->collation('ascii_bin');
                $rucAnterior->charset('ascii')->collation('ascii_bin');
                $estado->charset('ascii')->collation('ascii_bin');
            }

            $nroRuc->change();
            $dv->change();
            $rucAnterior->change();
            $estado->change();
        });

        Schema::table('ruc_paraguay_set', function (Blueprint $table) {
            if (!$this->indexExists('ruc_paraguay_set_lookup_idx')) {
                $table->index(['nro_ruc', 'digito_verificador'], 'ruc_paraguay_set_lookup_idx');
            }
            if (!$this->indexExists('ruc_paraguay_set_estado_idx')) {
                $table->index('estado', 'ruc_paraguay_set_estado_idx');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ruc_paraguay_set')) {
            return;
        }

        if ($this->isSqlite()) {
            Schema::drop('ruc_paraguay_set');
            Schema::create('ruc_paraguay_set', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('nro_ruc', 32)->nullable();
                $table->string('denominacion', 512)->nullable();
                $table->string('digito_verificador', 8)->nullable();
                $table->string('ruc_anterior', 32)->nullable();
                $table->string('estado', 40)->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table('ruc_paraguay_set', function (Blueprint $table) {
            if ($this->indexExists('ruc_paraguay_set_lookup_idx')) {
                $table->dropIndex('ruc_paraguay_set_lookup_idx');
            }
            if ($this->indexExists('ruc_paraguay_set_estado_idx')) {
                $table->dropIndex('ruc_paraguay_set_estado_idx');
            }
        });

        Schema::table('ruc_paraguay_set', function (Blueprint $table) {
            $table->string('nro_ruc', 32)->nullable()->change();
            $table->string('denominacion', 512)->nullable()->change();
            $table->string('digito_verificador', 8)->nullable()->change();
            $table->string('ruc_anterior', 32)->nullable()->change();
            $table->string('estado', 40)->nullable()->change();
            if (!Schema::hasColumn('ruc_paraguay_set', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    protected function rebuildSqliteTable(): void
    {
        Schema::drop('ruc_paraguay_set');
        Schema::create('ruc_paraguay_set', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nro_ruc', 8);
            $table->string('denominacion', 255);
            $table->char('digito_verificador', 1)->default('');
            $table->string('ruc_anterior', 16)->nullable();
            $table->string('estado', 20)->default('');
            $table->index(['nro_ruc', 'digito_verificador'], 'ruc_paraguay_set_lookup_idx');
            $table->index('estado', 'ruc_paraguay_set_estado_idx');
        });
    }

    protected function isMysql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }

    protected function isSqlite(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite';
    }

    protected function indexExists(string $index): bool
    {
        if (method_exists(Schema::class, 'hasIndex')) {
            return Schema::hasIndex('ruc_paraguay_set', $index);
        }

        $sm = Schema::getConnection()->getSchemaBuilder();

        return method_exists($sm, 'hasIndex') && $sm->hasIndex('ruc_paraguay_set', $index);
    }
}
