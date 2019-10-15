<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\UsuarioCompetencia;

class UsuarioCompetenciaQueriesTest extends KernelTestCase{

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

    // probamos la cantidad de organizadores de la competencia 6
    public function testOrganizatorsCompetition1(){
        $repository = $this->entityManager->getRepository(UsuarioCompetencia::class);

        $result = $repository->findOrganizatorsCompetencia(3);

        print_r($result);
        $this->assertEquals(count($result), 2);
    }

    // probamos la cantidad de organizadores de la competencia 6
    public function testOrganizatorsCompetition2(){
        $repository = $this->entityManager->getRepository(UsuarioCompetencia::class);

        $result = $repository->findOrganizatorsCompetencia(6);

        print_r($result);
        $this->assertEquals(count($result), 2);
    }

    // cant de solicitantes de una competencia, probamos con una competencia de 4 solicitantes
    public function testSolicitantesCompetition(){
        $repository = $this->entityManager->getRepository(UsuarioCompetencia::class);

        $result = $repository->findSolicitantesByCompetencia(3);

        print_r($result);
        $this->assertEquals(count($result), 4);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}