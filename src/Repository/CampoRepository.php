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

    // buscamos los campos de la competencia
    public function findFielsByCompetition($idCompetencia){
        $entityManager = $this->getEntityManager();

        // recuperamos los id de los predios de la competencia
        $subQuery = $entityManager->createQuery(
            ' SELECT DISTINCT p.id
            FROM App\Entity\PredioCompetencia pc 
            INNER JOIN App\Entity\Competencia c
            WITH pc.competencia = c.id
            INNER JOIN App\Entity\Predio p
            WITH pc.predio = p.id
            AND c.id = :idCompetencia
            ORDER BY p.id ASC
            ')->setParameter('idCompetencia',$idCompetencia);

        $resultQuery = $subQuery->execute();
        if(count($resultQuery) == 0){
            return null;
        }

        $arrayIdPredios = array();
        // pasamos solo los id de las competencias a un array
        foreach ($resultQuery as &$valor) {
            array_push($arrayIdPredios, $valor['id']);
        }
        // los pasamos a string para incorporarlo a la query
        $arrayIdPredios = implode(", ", $arrayIdPredios);
        $stringIdPredios = "(".$arrayIdPredios.")";
        $stringQueryWhere = ' WHERE p.id IN '.$stringIdPredios;

         // buscamos los datos de los campos
         $queryBase = ' SELECT DISTINCT c.id, c.nombre campo, p.nombre predio
         FROM App\Entity\Campo c
         INNER JOIN App\Entity\Predio p
         WITH c.predio = p.id
         '.$stringQueryWhere;

        $query = $entityManager->createQuery($queryBase);
                    
        return $query->execute();
    }
}
