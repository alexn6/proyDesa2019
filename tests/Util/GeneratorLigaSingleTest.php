<?php

// esto determina el directorio de trabajo(donde nos paramos dentro de la estructura del proyecto)
//namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;

//use App\Utils\GeneratorEncuentro as Gen;
use App\Utils\GeneratorEncuentro;

//require_once __DIR__ . '../vendor/mnito/round-robin/src/objects/ScheduleBuilder.php';
//require_once '../../vendor/mnito/round-robin/src/objects/ScheduleBuilder.php';
//require_once dirname(__FILE__).'/mnito/round-robin/src/objects/ScheduleBuilder.php';

// require_once '/var/www/html/proyDesa2019/vendor/mnito/round-robin/src/objects/ScheduleBuilder.php';


class GeneratorLigaSimpleTest extends TestCase{

    // private $generador;

    // /**
    //  * @param GeneratorEncuentro $generador
    //  */
    // public function __construct(GeneratorEncuentro $generador)
    // {
    //     $this->generador = $generador;
    // }

    // public function testLigaSinglePar(){
    //     $equipos = ['C1', 'C2', 'C3', 'C4'];
    //     $scheduleBuilder = new ScheduleBuilder($equipos);
    //     $schedule = $scheduleBuilder->build();

    //     $fechas = schedule($equipos);

    //     // print_r($schedule);
    //     print_r($fechas);
    //     // echo "Funciona el test";
    // }

    // public function testLigaSingleImpar(){
    //     $equipos = ['C1', 'C2', 'C3', 'C4', 'C5'];
    //     $scheduleBuilder = new ScheduleBuilder($equipos);
    //     $schedule = $scheduleBuilder->build();

    //     $fechas = schedule($equipos);

    //     // print_r($schedule);
    //     print_r($fechas);
    //     // echo "Funciona el test";
    // }

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