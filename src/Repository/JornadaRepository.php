<?php

namespace App\Repository;

use App\Entity\Jornada;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Jornada|null find($id, $lockMode = null, $lockVersion = null)
 * @method Jornada|null findOneBy(array $criteria, array $orderBy = null)
 * @method Jornada[]    findAll()
 * @method Jornada[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JornadaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Jornada::class);
    }

    // /**
    //  * @return Jornada[] Returns an array of Jornada objects
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
    public function findOneBySomeField($value): ?Jornada
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // recuperacion de la cantidad de jornadas de una competencia
    public function nJornadaCompetetion($idCompetencia){
        $entityManager = $this->getEntityManager();
        //SELECT DISTINCT MAX(`jornada`) as n_fechas FROM `encuentro` WHERE `competencia_id` = 3
        $query = $entityManager->createQuery(
            ' SELECT DISTINCT MAX(j.numero)
            FROM App\Entity\Jornada j
            WHERE j.competencia = :idCompetencia
            ')->setParameter('idCompetencia', $idCompetencia);

            
        return $query->execute();
    }

    // recuperamos la fecha de la jornada mas reciente de una competencia
    public function lastedByCompetition($idCompetencia){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            ' SELECT DISTINCT MAX(j.fecha)
            FROM App\Entity\Jornada j
            WHERE j.competencia = :idCompetencia
            ')->setParameter('idCompetencia', $idCompetencia);

            
        return $query->execute();
    }
}
