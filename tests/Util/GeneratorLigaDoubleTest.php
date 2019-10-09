<?php

use PHPUnit\Framework\TestCase;
use App\Utils\GeneratorEncuentro;

class GeneratorLigaDoubleTest extends TestCase{

    public function testLigaDoublePar(){
        $equipos = ['C1', 'C2', 'C3', 'C4'];
        $generador = new GeneratorEncuentro();
        
        $encuentros = $generador->ligaDouble($equipos);

        print_r($encuentros);
        $this->assertEquals(0, 0);
    }

    public function testLigaDoubleImpar(){
        $equipos = ['C1', 'C2', 'C3', 'C4', 'C5'];
        $generador = new GeneratorEncuentro();
        
        $encuentros = $generador->ligaDouble($equipos);

        print_r($encuentros);
        $this->assertEquals(0, 0);
    }
}