<?php

namespace martinpa13py\RUCParaguay\Tests;

use Illuminate\Support\Facades\Storage;
use martinpa13py\RUCParaguay\Models\RucParaguaySet;
use martinpa13py\RUCParaguay\Services\RUCParaguay;
use martinpa13py\RUCParaguay\Services\RUCParaguayUpdater;

class UpdaterAndSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::disk('local')->makeDirectory('ruc-paraguay');
    }

    public function test_limit_imports_only_n_rows_from_local_fixture(): void
    {
        $this->writeFixture([
            '80000001|EMPRESA UNO|1||ACTIVO',
            '80000002|EMPRESA DOS|2||ACTIVO',
            '80000003|EMPRESA TRES|3||CANCELADO',
            '80000004|EMPRESA CUATRO|4||ACTIVO',
        ]);

        $updater = app(RUCParaguayUpdater::class);
        $updater->setOutput(fn () => null);
        $updater->setSkipDownload(true);
        $updater->setFiles([0]);
        $updater->setLimit(2);
        $updater->run();

        $this->assertSame(2, RucParaguaySet::count());
        $this->assertSame('80000001', RucParaguaySet::orderBy('id')->first()->nro_ruc);
    }

    public function test_refuses_sample_import_when_table_has_rows_without_force(): void
    {
        RucParaguaySet::insert([
            'nro_ruc' => '111',
            'denominacion' => 'EXISTENTE',
            'digito_verificador' => '1',
            'ruc_anterior' => null,
            'estado' => 'ACTIVO',
        ]);

        $messages = [];
        $updater = app(RUCParaguayUpdater::class);
        $updater->setOutput(function (string $msg) use (&$messages) {
            $messages[] = $msg;
        });
        $updater->setSkipDownload(true);
        $updater->setLimit(10);
        $updater->run();

        $this->assertSame(1, RucParaguaySet::count());
        $this->assertTrue(collect($messages)->contains(fn ($m) => str_contains($m, 'ya tiene datos')));
    }

    public function test_search_uses_exact_ruc_and_limit(): void
    {
        RucParaguaySet::insert([
            [
                'nro_ruc' => '460018',
                'denominacion' => 'CASTILLO PABLO',
                'digito_verificador' => '3',
                'ruc_anterior' => null,
                'estado' => 'ACTIVO',
            ],
            [
                'nro_ruc' => '460019',
                'denominacion' => 'OTRO',
                'digito_verificador' => '4',
                'ruc_anterior' => null,
                'estado' => 'ACTIVO',
            ],
            [
                'nro_ruc' => '1828480A',
                'denominacion' => 'PEREZ, JUAN',
                'digito_verificador' => '1',
                'ruc_anterior' => null,
                'estado' => 'ACTIVO',
            ],
        ]);

        $exact = RUCParaguay::search('460018-3');
        $this->assertCount(1, $exact);
        $this->assertSame('CASTILLO PABLO', $exact->first()->denominacion);

        $letter = RUCParaguay::search('1828480A');
        $this->assertCount(1, $letter);

        $byName = RUCParaguay::search('CASTILLO');
        $this->assertCount(1, $byName);

        $capped = RUCParaguay::search('46', 1);
        $this->assertCount(1, $capped);
    }

    /**
     * @param list<string> $lines
     */
    protected function writeFixture(array $lines): void
    {
        Storage::disk('local')->put('ruc-paraguay/ruc0.txt', implode("\n", $lines) . "\n");
    }
}
