<?php

namespace App\Utils;

use App\Utils\GeneratorEncuentro;

class CreatorEncuentros{

    // private const TYPES_ORGANIZATION = array(
    //     'ELIM' => "Eliminatorias",
    //     'LIGSING' => "Liga Single",
    //     'LIGDOUB' => "Liga Double",
    //     'ELIMDOUB' => "Eliminatorias Doubles",
    //     'FASEGRUP' => "Fase Grupos"
    // );

    // realiza los enfrentamientos de una competencia dependiendo el tipo de organizacion
    public function createMatches($equipos, $nomb_tipoorg, $cant_grupos){
        // $tipo_org = self::TYPES_ORGANIZATION[$nomb_tipoorg];
        $generador = new GeneratorEncuentro();
        $encuentros;
        
        switch ($nomb_tipoorg) {
            case 'ELIM':
                $encuentros = $generador->eliminatorias($equipos);
                return $encuentros;
                break;
            case 'LIGSING':
                $encuentros = $generador->ligaSingle($equipos);
                return $encuentros;
                break;
            case 'LIGDOUB':
                $encuentros = $generador->ligaDouble($equipos);
                return $encuentros;
                break;
            case 'ELIMDOUB':
                $encuentros = $generador->eliminatoriasDoubles($equipos);
                return $encuentros;
                break;
            case 'FASEGRUP':
                // ver como resolver la cant de equipos
                $encuentros = $generador->faseGrupos($equipos, $cant_grupos);
                return $encuentros;
                break;
        }

        // return $encuentros;
    }
}