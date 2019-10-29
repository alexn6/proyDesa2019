<?php

// esto determina el directorio de trabajo(donde nos paramos dentro de la estructura del proyecto)
//namespace App\Tests\Util;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Controller\EncuentroController;

use App\Entity\Competencia;

class EncuentroSaveTest extends KernelTestCase{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testSaveLigaSingle(){

        $repositoryComp = $this->entityManager->getRepository(Competencia::class);
        $competencia = $repositoryComp->find(5);

        if($competencia == NULL){
            echo("No existe la competencia.\n");
        }
        else{
            echo("Competencia encontrada.\n");
        }

        //echo("sdfsdfs\n");

        $fechas = array();
        $fecha1 = array();
        $fecha2 = array();
        
        array_push($fecha1, ["c1", "c2"]);
        array_push($fecha1, ["c5", "c3"]);
        array_push($fecha2, ["c11", "c4"]);
        array_push($fecha2, ["c15", "c9"]);
        //print_r($fecha1); 
        array_push($fechas, $fecha1);
        array_push($fechas, $fecha2);

        $controller = new EncuentroController();
        $controller->saveLiga($fechas, $competencia);

        //echo("Van los encuentros:\n");
        //print_r($fechas);
        $this->assertEquals(2, 3);
    }

    // public function saveLigaSingle1(){
    //     echo("kjsbndsksb");
    //     $this->assertEquals(2, 4);
    // }

     // probamos la cantidad de organizadores de la competencia 6
    //  public function testOrganizatorsCompetition1(){
    //     $repository = $this->entityManager->getRepository(UsuarioCompetencia::class);

    //     $result = $repository->findOrganizatorsCompetencia(3);

    //     print_r($result);
    //     $this->assertEquals(count($result), 2);
    // }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}