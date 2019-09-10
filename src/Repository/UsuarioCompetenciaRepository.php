<?php

namespace App\Repository;

use App\Entity\UsuarioCompetencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UsuarioCompetencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioCompetencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioCompetencia[]    findAll()
 * @method UsuarioCompetencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioCompetenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioCompetencia::class);
    }

    // /**
    //  * @return UsuarioCompetencia[] Returns an array of UsuarioCompetencia objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UsuarioCompetencia
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
