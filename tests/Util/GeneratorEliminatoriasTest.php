<?php

use PHPUnit\Framework\TestCase;
use App\Utils\GeneratorEncuentro;

class GeneratorEliminatoriasTest extends TestCase{

    public function testEliminatorias(){
        $equipos = ['C1', 'C2', 'C3', 'C4'];
        $generador = new GeneratorEncuentro();
        $encuentros = $generador->eliminatorias($equipos);

        print_r($encuentros);

        $this->assertEquals(0, 0);
    }
    
    // para correr el test
    // php bin/phpunit tests/Util/GeneratorLigaDoubleTest.php 
}