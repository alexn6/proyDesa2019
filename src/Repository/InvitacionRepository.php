<?php

namespace App\Repository;

use App\Entity\Invitacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Invitacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitacion[]    findAll()
 * @method Invitacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitacion::class);
    }

    // /**
    //  * @return Invitacion[] Returns an array of Invitacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Invitacion
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
