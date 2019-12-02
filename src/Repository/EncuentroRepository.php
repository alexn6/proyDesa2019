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

    const COMPETIDOR1 = 1;
    const COMPETIDOR2 = 2;

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

    // recuperamos los encuentros de una competencia por determinada fase
    public function findEncuentrosByCompetenciaFase($idCompetencia, $fase)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT e
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\Jornada j
                WITH e.jornada = j.id
                WHERE e.competencia = :idCompetencia
                AND j.fase = :fase
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('fase', $fase);

        return $query->execute();
    }

    // recuperamos los encuentros de una competencia, por fase y grupo
    public function findEncuentrosByCompetenciaFaseGrupo($idCompetencia, $fase, $grupo)
    {
        $entityManager = $this->getEntityManager();
        // le adjuntamos la parte generica/unica de la consulta
        $stringQuery = ' SELECT e
                            FROM App\Entity\Encuentro e
                            WHERE e.competencia = :idCompetencia
                        ';
        // si recibimos parametros agrandamos la consulta
        if($fase != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryFase = ' AND e.jornada = '.$fase;
            $stringQuery = $stringQuery.$stringQueryFase;
        }

        if($grupo != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryGrupo = ' AND e.grupo = '.$grupo;
            $stringQuery = $stringQuery.$stringQueryGrupo;
        }

        $query = $entityManager->createQuery($stringQuery);
        // seteamos la competencia
        $query->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }

    // recuperamos los encuentros del competidor1 segun fecha y fase
    public function findEncuentrosComp1ByCompetencia($idCompetencia, $fase, $grupo)
    {
        $entityManager = $this->getEntityManager();

        // recuperamos el string de la query armado
        $stringQuery = $this->getStringQueryConfrontationsByCompetitorsFull($this::COMPETIDOR1, $fase, $grupo);
        
        $query = $entityManager->createQuery($stringQuery);
        // seteamos la competencia
        $query->setParameter('idCompetencia', $idCompetencia);
                    
        return $query->execute();
    }

    // recuperamos los encuentros del competidor2 segun fecha y fase
    public function findEncuentrosComp2ByCompetencia($idCompetencia, $fase, $grupo)
    {
        $entityManager = $this->getEntityManager();

        // recuperamos el string de la query armado
        $stringQuery = $this->getStringQueryConfrontationsByCompetitorsFull($this::COMPETIDOR2, $fase, $grupo);
        
        $query = $entityManager->createQuery($stringQuery);
        // seteamos la competencia
        $query->setParameter('idCompetencia', $idCompetencia);
                    
        return $query->execute();
    }

    // ##################################################################################
    // ############################## funciones auxiliares ##############################

    // crea la consulta de encuentros por competencia, fase y grupo de los competidores
    private function getStringQueryConfrontationsByCompetitorsFull($competidor, $fase, $grupo){
        $stringQuery;
        // creamos el select de la consulta
        if($competidor == $this::COMPETIDOR1){
            $stringQuery = ' SELECT DISTINCT e, uc.alias competidor1';
        }
        if($competidor == $this::COMPETIDOR2){
            $stringQuery = ' SELECT DISTINCT e, uc.alias competidor2';
        }
        // le adjuntamos la parte generica/unica de la consulta
        $stringQuery = $stringQuery.' FROM App\Entity\Encuentro e
                            INNER JOIN App\Entity\UsuarioCompetencia uc
                            WITH e.competencia = uc.competencia
                            WHERE e.competencia = :idCompetencia
                            AND uc.competencia = :idCompetencia
                        ';

        $stringQueryCompetidor;
        // seteamos el competidor
        if($competidor == $this::COMPETIDOR1){
            $stringQueryCompetidor = ' AND e.competidor1 = uc.id';
        }
        if($competidor == $this::COMPETIDOR2){
            $stringQueryCompetidor = ' AND e.competidor2 = uc.id';
        }
        $stringQuery = $stringQuery.$stringQueryCompetidor;

        // si recibimos parametros agrandamos la consulta
        if($fase != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryFase = ' AND e.jornada = '.$fase;
            $stringQuery = $stringQuery.$stringQueryFase;
        }

        if($grupo != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryGrupo = ' AND e.grupo = '.$grupo;
            $stringQuery = $stringQuery.$stringQueryGrupo;
        }

        // var_dump($stringQuery);
        return $stringQuery;
    }
}
