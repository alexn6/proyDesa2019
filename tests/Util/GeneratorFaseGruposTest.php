<?php

use PHPUnit\Framework\TestCase;
use App\Utils\GeneratorEncuentro;

class GeneratorFaseGruposTest extends TestCase{

    // genera la division de grupos y los encuentros de los mismos
    public function testFaseGrupos(){
        $equipos = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'];
        $generador = new GeneratorEncuentro();
        $encuentros = $generador->faseGrupos($equipos, 2);

        print_r($encuentros);

        $this->assertEquals(0, 0);
    }
    
}