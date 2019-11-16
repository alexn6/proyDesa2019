<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Predio;
use App\Entity\Competencia;

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

    //Recuperar predios por id de competencia
    public function findGroundsByCompetetition($idCompetencia, $idPredio){
        $entityManager = $this->getEntityManager();
       
        $stringQueryBase = 'SELECT p
                            FROM App\Entity\Predio p
                            INNER JOIN App\Entity\Competencia c
                            WITH p.competencia = c.id
                            AND c.id =' .$idCompetencia;
                        
        if($idPredio != NULL){
            $stringQueryPredio = ' AND p.id = '.$idPredio;
            $stringQueryBase = $stringQueryBase.$stringQueryPredio;
                
        }
        $query = $entityManager->createQuery($stringQueryBase);    
        return $query->execute();   
    }

}
