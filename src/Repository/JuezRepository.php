<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Juez;
use App\Entity\Competencia;

/**
 * @method Juez|null find($id, $lockMode = null, $lockVersion = null)
 * @method Juez|null findOneBy(array $criteria, array $orderBy = null)
 * @method Juez[]    findAll()
 * @method Juez[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JuezRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Juez::class);
    }

    // /**
    //  * @return Juez[] Returns an array of Juez objects
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
    public function findOneBySomeField($value): ?Juez
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // Recuperar jueces por id de competencia
    // public function findJudgesByCompetetition($idCompetencia){
    //     $entityManager = $this->getEntityManager();
    //     $query = $entityManager->createQuery(
    //     '   SELECT j
    //         FROM App\Entity\Juez j
    //         INNER JOIN App\Entity\Competencia c
    //         WITH j.competencia = c.id
    //         AND c.id = :idCompetencia
    //     ')->setParameter('idCompetencia',$idCompetencia);
        
    //     return $query->execute();   
    // }
}
