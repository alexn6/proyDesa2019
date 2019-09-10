<?php

namespace App\Repository;

use App\Entity\Competencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Competencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competencia[]    findAll()
 * @method Competencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competencia::class);
    }

    // /**
    //  * @return Competencia[] Returns an array of Competencia objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Competencia
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
