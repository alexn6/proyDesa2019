<?php

// esto determina el directorio de trabajo(donde nos paramos dentro de la estructura del proyecto)
//namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Utils\GeneratorEncuentro;

class GeneratorLigaSimpleTest extends TestCase{

    public function testLigaSinglePar(){
        $equipos = ['C1', 'C2', 'C3', 'C4'];
        $generador = new GeneratorEncuentro();
        $encuentros = $generador->ligaSingle($equipos);

        print_r($encuentros);

        $this->assertEquals(0, 0);
    }

    public function testLigaSingleImpar(){
        $equipos = ['C1', 'C2', 'C3', 'C4', 'C5'];
        $generador = new GeneratorEncuentro();
        $encuentros = $generador->ligaSingle($equipos);

        // $encuentros = $this->generador->ligaSingle($equipos);

        print_r($encuentros);

        $this->assertEquals(0, 0);
    }
}