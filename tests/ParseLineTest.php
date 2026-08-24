<?php

namespace martinpa13py\RUCParaguay\Tests;

use martinpa13py\RUCParaguay\Services\RUCParaguayUpdater;

class ParseLineTest extends TestCase
{
    public function test_parses_standard_pipe_line(): void
    {
        $updater = new RUCParaguayUpdater(fn () => null);

        $row = $updater->parseLine("80000001|EMPRESA DE PRUEBA SA|5||ACTIVO\n");

        $this->assertSame([
            'nro_ruc' => '80000001',
            'denominacion' => 'EMPRESA DE PRUEBA SA',
            'digito_verificador' => '5',
            'ruc_anterior' => null,
            'estado' => 'ACTIVO',
        ], $row);
    }

    public function test_keeps_letter_suffix_on_ruc(): void
    {
        $updater = new RUCParaguayUpdater(fn () => null);

        $row = $updater->parseLine('1828480A|PEREZ, JUAN|1|CAGJ761720E|CANCELADO');

        $this->assertSame('1828480A', $row['nro_ruc']);
        $this->assertSame('1', $row['digito_verificador']);
        $this->assertSame('CAGJ761720E', $row['ruc_anterior']);
    }

    public function test_merges_extra_pipes_into_denominacion(): void
    {
        $updater = new RUCParaguayUpdater(fn () => null);

        $row = $updater->parseLine('80000002|NOMBRE CON | PIPE|4|OLD99|ACTIVO');

        $this->assertSame('80000002', $row['nro_ruc']);
        $this->assertSame('NOMBRE CON | PIPE', $row['denominacion']);
        $this->assertSame('4', $row['digito_verificador']);
        $this->assertSame('OLD99', $row['ruc_anterior']);
        $this->assertSame('ACTIVO', $row['estado']);
    }

    public function test_rejects_short_lines(): void
    {
        $updater = new RUCParaguayUpdater(fn () => null);

        $this->assertNull($updater->parseLine('solo|tres|campos'));
        $this->assertNull($updater->parseLine(''));
    }
}
