<?php

namespace App\Repository;

use App\Entity\Predio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Predio|null find($id, $lockMode = null, $lockVersion = null)
 * @method Predio|null findOneBy(array $criteria, array $orderBy = null)
 * @method Predio[]    findAll()
 * @method Predio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PredioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Predio::class);
    }

    // /**
    //  * @return Predio[] Returns an array of Predio objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Predio
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
