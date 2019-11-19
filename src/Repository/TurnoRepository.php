<?php

namespace App\Repository;

use App\Entity\Turno;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Turno|null find($id, $lockMode = null, $lockVersion = null)
 * @method Turno|null findOneBy(array $criteria, array $orderBy = null)
 * @method Turno[]    findAll()
 * @method Turno[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TurnoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Turno::class);
    }

    // /**
    //  * @return Turno[] Returns an array of Turno objects
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
    public function findOneBySomeField($value): ?Turno
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function validarTurno($idCompetencia, $hora_desde, $hora_hasta){

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
        '   SELECT t
            FROM App\Entity\Turno t
            INNER JOIN App\Entity\Competencia c
            WITH c.id = t.competencia 
            AND c.id = :id_competencia
        ')->setParameter('id_competencia',$idCompetencia);
        

<<<<<<< HEAD
        //return $query->execute();
        return null;
=======
        return $query->execute();   
    }
>>>>>>> 801779b47b245e5349a9d8d09465d1dda9b1d2a9

       //Recuperar turnos por id de competencia
       public function findTurnByCompetetition($idCompetencia, $idTurno){
        $entityManager = $this->getEntityManager();
       
        $stringQueryBase = 'SELECT t.id, t.hora_desde, t.hora_hasta 
                            FROM App\Entity\Turno t
                            INNER JOIN App\Entity\Competencia c
                            WITH t.competencia = c.id
                            AND c.id =' .$idCompetencia;
                        
        if($idTurno != NULL){
            $stringQueryTurno = ' AND t.id = '.$idTurno;
            $stringQueryBase = $stringQueryBase.$stringQueryTurno;
                
        }

        $query = $entityManager->createQuery($stringQueryBase);    
        return $query->execute();   
    }

}
