<?php

namespace App\Repository;

use App\Entity\TipoOrganizacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TipoOrganizacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoOrganizacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoOrganizacion[]    findAll()
 * @method TipoOrganizacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoOrganizacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoOrganizacion::class);
    }

    // /**
    //  * @return TipoOrganizacion[] Returns an array of TipoOrganizacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TipoOrganizacion
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
