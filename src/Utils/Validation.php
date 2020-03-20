<?php

namespace App\Utils;

use App\Utils\Constant;

class Validation{

    // validamos que se cumpla la cant minima de competidores de una eliminatoria
    public function validarCompetidoresEliminitorias($n_fase, $n_minima, $n_grupos, $n_competidores){
        // la cant de competidores depende de la fase
        $cant_justa = pow(2, $n_fase);

        if($n_competidores != $cant_justa){
            // return false;
            return $cant_justa;
        }
        return true;
    }

    public function validarCompetidoresLiga($n_fase, $n_minima, $n_grupos, $n_competidores){
        $cant_minima;
        if($n_minima == null){
            $cant_minima = Constant::MIN_COMPETIDORES_LIGA;
        }else{
            $cant_minima = $n_minima;
        }

        if($n_competidores < $cant_minima){
            // return false;
            return $cant_minima;
        }
        return true;
    }

    public function validarCompetidoresGrupos($n_fase, $n_minima, $n_grupos, $n_competidores){
        if($n_grupos < 2){
            return false;
        }
        $cant_minima_grupo;
        $cant_minima;

        if($n_minima == null){
            $cant_minima_grupo = Constant::MIN_COMPETIDORES_LIGA;
        }else{
            $cant_minima_grupo = $n_minima;
        }

        $cant_minima = $n_grupos * $cant_minima_grupo;
        
        if($n_competidores < $cant_minima){
            //return false;
            return $cant_minima;
        }
        return true;
    }

}