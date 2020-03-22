<?php

namespace App\Utils;

require_once '/var/www/html/proyDesa2019/vendor/mnito/round-robin/src/objects/ScheduleBuilder.php';

class GeneratorEncuentro{

    // recibe una lista de competidores y devuelve los encuentros unicos de la liga
    public function ligaSingle($equipos){
        $encuentros = schedule($equipos);
        // print_r($encuentros);
        return $encuentros;
    }
    
    // recibe una lista de competidores y devuelve los 2 encuentros de la liga
    public function ligaDouble($equipos){
        $rondas = (($count = count($equipos)) % 2 === 0 ? $count - 1 : $count) * 2;
        $encuentros = schedule($equipos, $rondas);
        //print_r($encuentros);
        return $encuentros;
    }

    // recibe una lista de competidores y devuelve los cruces eliminatorios de un unico encuentro
    public function eliminatorias($equipos){
        $rondas = 1;
        $encuentros = schedule($equipos, $rondas);

        return $encuentros;
    }

    // recibe una lista de competidores y devuelve los cruces eliminatorios a 2 encuentros
    public function eliminatoriasDoubles($equipos){
        // generamos un primer curce entre los equipos
        $rondas = 1;
        $encuentros = schedule($equipos, $rondas);

        // recuperamos los cruces y cambiamos de lugar los competidores de los openentes
        $encuentros_ida = $encuentros[1];
        $encuentros_vuelta = array();

        for ($i=0; $i < count($encuentros_ida); $i++) { 
            $encuentro = $encuentros_ida[$i];
            array_push($encuentros_vuelta, [$encuentro[1], $encuentro[0]]);
        }

        array_push($encuentros, $encuentros_vuelta);

        return $encuentros;
    }

    // realiza la division aleatoria de los competidores en grupos dependiendo de
    // de la cant de grupos recibida y genera los encuentros de cada grupo
    public function faseGrupos($equipos, $cantGrupos){
        $comp_grupo = count($equipos)/$cantGrupos;
        // alteramos el orden de los equipos recibidos y los seperamos en grupos
        shuffle($equipos);
        $grupos = array_chunk($equipos, $comp_grupo);

        $encuentros_grupos = array();
        // creamos los cruces de cada grupo
        // $i = 1;
        foreach ($grupos as $equipos_por_grupo) {
            // $nombreGrupo = "Grupo Nº".$i;
            $encuentros_por_grupo["Competidores"] = $equipos_por_grupo;
            $encuentros_por_grupo["Encuentros"] = schedule($equipos_por_grupo);
            array_push($encuentros_grupos, $encuentros_por_grupo);
        }

        return $encuentros_grupos;
    }

}