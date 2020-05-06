<?php

namespace App\Utils;

class ParamsProject{

    // devolvemos el path de la carpeta del proyecto
    public function getRoot(){
        return $path = dirname(__DIR__, 2);;
    }

}