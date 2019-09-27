<?php

namespace App\Utils;

require_once '/var/www/html/proyDesa2019/vendor/mnito/round-robin/src/objects/ScheduleBuilder.php';

class GeneratorEncuentro{

    public function ligaSingle($equipos){
    
        $encuentros = schedule($equipos);
        // print_r($encuentros);
        return $encuentros;
    }
    
    public function ligaDouble($equipos){
        
        $rondas = (($count = count($equipos)) % 2 === 0 ? $count - 1 : $count) * 2;
        $encuentros = schedule($equipos, $rondas);
        //print_r($encuentros);
        return $encuentros;
    }

}