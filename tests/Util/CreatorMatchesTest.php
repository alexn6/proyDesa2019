<?php

use PHPUnit\Framework\TestCase;
use App\Utils\CreatorEncuentros;

class CreatorMatchesTest extends TestCase{

    public function testMatchesLigaSingle(){
        $equipos = ['C1', 'C2', 'C3', 'C4'];
        $creator = new CreatorEncuentros();
        $nomb_tipo_org = "LIGSING";
        $encuentros = $creator->createMatches($equipos, $nomb_tipo_org, null);
        echo"##################### LIGA SINGLE ######################\n";
        print_r($encuentros);
        echo"########################################################\n";

        // verificamos la cant de jornadas generadas
        $this->assertEquals(count($encuentros), 3);
    }

    public function testMatchesEliminatoriasDoubles(){
        $equipos = ['C1', 'C2', 'C3', 'C4'];
        $creator = new CreatorEncuentros();
        $nomb_tipo_org = "ELIMDOUB";
        $encuentros = $creator->createMatches($equipos, $nomb_tipo_org, null);
        echo"################ ELIMINATORIAS DOUBLES #################\n";
        print_r($encuentros);
        echo"########################################################\n";

        // verificamos que se hayan generado las 2 fases/jornadas
        $this->assertEquals(count($encuentros), 2);
    }

    public function testMatchesFaseGrupos(){
        $equipos = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'];
        $creator = new CreatorEncuentros();
        $nomb_tipo_org = "FASEGRUP";
        $cant_grupos = 2;
        $encuentros = $creator->createMatches($equipos, $nomb_tipo_org, $cant_grupos);
        echo"##################### FASE DE GRUPOS ###################\n";
        print_r($encuentros);
        echo"########################################################\n";

        // controlamos la cant de grupos generados
        $this->assertEquals(count($encuentros), 2);
        // controlamos la cant de jornadas por grupo
        $this->assertEquals(count($encuentros[0]["Encuentros"]), 3);
    }
}