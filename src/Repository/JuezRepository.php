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

    // recupera jueces con nombre que contengan lo recibdo
    public function getLikeName($name, $apellido){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
        '   SELECT j
            FROM App\Entity\Juez j
            WHERE (j.nombre LIKE :nameJudge OR j.apellido LIKE :apellido)
            ')->setParameter('nameJudge','%'.$name.'%')
            ->setParameter('apellido','%'.$apellido.'%');
        
        return $query->execute();
    }

    // recupera jueces con nombre y/o apellido que contengan lo recibdo
    public function getLikeNameLastname($name, $apellido){
        $entityManager = $this->getEntityManager();

        $queryBase = ' SELECT j
                        FROM App\Entity\Juez j';

        // si nos mandan algun dato
        if(($name != null) || ($apellido != null)){
            if(($name != null) && ($apellido != null)){
                $queryBase = $queryBase.' WHERE (j.nombre LIKE :nameJudge AND j.apellido LIKE :apellido)';
            }
            else{
                if($apellido != null){
                    $queryBase = $queryBase.' WHERE j.apellido LIKE :apellido';
                }
                else{
                    $queryBase = $queryBase.' WHERE j.nombre LIKE :nameJudge';
                }
            }
        }

        $query = $entityManager->createQuery($queryBase);
        
        if($name != null){
            $query->setParameter('nameJudge','%'.$name.'%');
        }
        if($apellido != null){
            $query->setParameter('apellido','%'.$apellido.'%');
        }
            
        return $query->execute();
    }
}
