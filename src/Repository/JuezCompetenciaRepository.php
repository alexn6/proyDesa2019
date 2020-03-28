<?php

namespace App\Repository;

use App\Entity\JuezCompetencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method JuezCompetencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method JuezCompetencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method JuezCompetencia[]    findAll()
 * @method JuezCompetencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JuezCompetenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JuezCompetencia::class);
    }

    // Recupera jueces por id de competencia
    public function refereesByCompetetition($idCompetencia){
        $entityManager = $this->getEntityManager();
       
        // $stringQueryBase = 'SELECT j.id, j.nombre, j.apellido, j.dni 
        //                     FROM App\Entity\JuezCompetencia jc
        //                     INNER JOIN App\Entity\Competencia c
        //                     WITH jc.competencia = c.id
        //                     INNER JOIN App\Entity\Juez j
        //                     WITH jc.juez = j.id
        //                     AND c.id =' .$idCompetencia;

        // $query = $entityManager->createQuery($stringQueryBase);

        $query = $entityManager->createQuery(
            'SELECT j.id, j.nombre, j.apellido, j.dni 
            FROM App\Entity\JuezCompetencia jc
            INNER JOIN App\Entity\Competencia c
            WITH jc.competencia = c.id
            INNER JOIN App\Entity\Juez j
            WITH jc.juez = j.id
            AND c.id =' .$idCompetencia
        );  
        return $query->execute();   
    }

    // /**
    //  * @return JuezCompetencia[] Returns an array of JuezCompetencia objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JuezCompetencia
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
