<?php

namespace App\Repository;

use App\Entity\PredioCompetencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PredioCompetencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method PredioCompetencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method PredioCompetencia[]    findAll()
 * @method PredioCompetencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PredioCompetenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PredioCompetencia::class);
    }

    //Recuperar predios por id de competencia
    public function groundsByCompetetition($idCompetencia){
        $entityManager = $this->getEntityManager();
       
        $stringQueryBase = 'SELECT p.id, p.nombre, p.direccion, cd.nombre as ciudad 
                            FROM App\Entity\PredioCompetencia pc
                            INNER JOIN App\Entity\Competencia c
                            WITH pc.competencia = c.id
                            INNER JOIN App\Entity\Predio p
                            WITH pc.predio = p.id
                            INNER JOIN App\Entity\Ciudad cd
                            WITH cd.id = p.ciudad
                            AND c.id =' .$idCompetencia;
        $query = $entityManager->createQuery($stringQueryBase);    
        return $query->execute();   
    }
}
