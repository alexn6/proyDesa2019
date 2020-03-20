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
        
        //return $query->execute();
        return null;
    }

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

    // controlamos que entre un conjunto de turnos no exista ningun turno, fecha inicio turnos
    public function findTurnInitBetween($idCompetencia, $horaIniSet, $horaFinSet){
        $entityManager = $this->getEntityManager();
        // $stringQuery = '   SELECT t
        //                 FROM App\Entity\Turno t
        //                 INNER JOIN App\Entity\Competencia c
        //                 WITH c.id = t.competencia 
        //                 AND c.id = :idCompetencia
        //                 AND (t.hora_desde >= \'18:00:00\' AND t.hora_desde <= \'20:36:00\')
        //                 ';
        $stringQuery = '   SELECT t
                            FROM App\Entity\Turno t
                            INNER JOIN App\Entity\Competencia c
                            WITH c.id = t.competencia 
                            AND c.id = :idCompetencia
                            AND (t.hora_desde >= \''.$horaIniSet.'\' AND t.hora_desde <= \''.$horaFinSet.'\')
                            ';
        var_dump($stringQuery);
        $query = $entityManager->createQuery($stringQuery)->setParameter('idCompetencia',$idCompetencia);
        
        return $query->execute();
    }

    // controlamos que entre un conjunto de turnos o exista ningun turno, fecha fin turnos
    public function findTurnEndBetween($idCompetencia, $horaIniSet, $horaFinSet){
        $entityManager = $this->getEntityManager();
        $stringQuery = '   SELECT t
                            FROM App\Entity\Turno t
                            INNER JOIN App\Entity\Competencia c
                            WITH c.id = t.competencia 
                            AND c.id = :idCompetencia
                            AND (t.hora_hasta >= \''.$horaIniSet.'\' AND t.hora_hasta <= \''.$horaFinSet.'\')
                            ';
        var_dump($stringQuery);
        $query = $entityManager->createQuery($stringQuery)->setParameter('idCompetencia',$idCompetencia);
        
        return $query->execute();
    }

}
