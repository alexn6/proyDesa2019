<?php

use PHPUnit\Framework\TestCase;
use App\Utils\GeneratorEncuentro;

class GeneratorEliminatoriasDoublesTest extends TestCase{

    public function testEliminatoriasDoubles(){
        $equipos = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'];
        $generador = new GeneratorEncuentro();
        $encuentros = $generador->eliminatoriasDoubles($equipos);

        print_r($encuentros);

        $this->assertEquals(0, 0);
    }
    
}