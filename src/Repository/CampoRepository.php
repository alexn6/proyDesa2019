<?php

namespace App\Repository;

use App\Entity\Campo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Campo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campo[]    findAll()
 * @method Campo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campo::class);
    }

    // /**
    //  * @return Campo[] Returns an array of Campo objects
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
    public function findOneBySomeField($value): ?Campo
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findFieldsByGrounds($idPredio, $idCampo){
        $entityManager = $this->getEntityManager();
      
        $stringQueryBase = 'SELECT c
                            FROM App\Entity\Campo c
                            INNER JOIN App\Entity\Predio p
                            WITH c.predio = p.id
                            AND p.id =' .$idPredio; 
        if($idCampo != NULL){
            $stringQueryCampo = ' AND c.id = '.$idCampo;
            $stringQueryBase = $stringQueryBase.$stringQueryCampo;
        }
        $query = $entityManager->createQuery($stringQueryBase);    
        return $query->execute();   
    }
    
    public function findFieldsByName($idPredio, $nameField){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT c
                FROM App\Entity\Campo c
                INNER JOIN App\Entity\Predio p
                WITH c.predio = p.id
                AND p.id = :idPredio
                AND c.nombre = :nameField
            ')->setParameter('idPredio',$idPredio)
              ->setParameter('nameField',$nameField);
        
        return $query->execute();   
    }
}