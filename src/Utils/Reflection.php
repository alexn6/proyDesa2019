<?php

namespace App\Utils;

class Reflection{

    // Recibe el nombre de una funcion de un objeto y el mismo, y devuelve
    // la funcion lista para ser ejectuda
    public function getFunction($object, $stringMethod){
        return function() use($object, $stringMethod){
            $args = func_get_args();
            return call_user_func_array(array($object, $stringMethod), $args);
        };
    }

}