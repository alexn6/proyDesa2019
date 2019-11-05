<?php

namespace App\Repository;

use App\Entity\Encuentro;
use App\Entity\Competencia;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Encuentro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Encuentro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Encuentro[]    findAll()
 * @method Encuentro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EncuentroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Encuentro::class);
    }

    // /**
    //  * @return Encuentro[] Returns an array of Encuentro objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Encuentro
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // recuperamos los encuentros de una competencia
    public function findEncuentrosByCompetencia($idCompetencia)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT e
                FROM App\Entity\Encuentro e
                WHERE e.competencia = :idCompetencia
            ')->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }

    // recuperamos el competidor1 de una competencia
    public function findEncuentrosComp1ByCompetencia($idCompetencia)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT DISTINCT e.id , uc.alias competidor1
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\UsuarioCompetencia uc
                WITH e.competencia = uc.competencia
                WHERE e.competencia = :idCompetencia
                AND uc.competencia = :idCompetencia
                AND e.competidor1 = uc.usuario
            ')->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }

    // recuperamos el competidor1 de una competencia
    public function findEncuentrosComp2ByCompetencia($idCompetencia)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT DISTINCT e.id , uc.alias competidor2
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\UsuarioCompetencia uc
                WITH e.competencia = uc.competencia
                WHERE e.competencia = :idCompetencia
                AND uc.competencia = :idCompetencia
                AND e.competidor2 = uc.usuario
            ')->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }
}
