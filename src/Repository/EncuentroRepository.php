<?php

namespace App\Repository;

use App\Entity\Encuentro;
use App\Entity\Jornada;
use App\Entity\Competencia;
use App\Entity\UsuarioCompetencia;

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

    // recuperamos los encuentros de una competencia por jornada y grupo
    public function findEncuentrosByCompetenciaJornadaGrupo($idCompetencia, $fase, $grupo)
    {
        $entityManager = $this->getEntityManager();

        $stringQuery = ' SELECT DISTINCT e
                            FROM App\Entity\Encuentro e
                            INNER JOIN App\Entity\Competencia c
                            WITH e.competencia = c.id
                            WHERE e.competencia = :idCompetencia
                        ';
        // si recibimos parametros agrandamos la consulta
        if($fase != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            // TODO: agregar tabla JORNADA y buscar por su numero
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

    // Pre: solo para tipo eliminatoria y liga
    // Buscamos encuentros de una fase, de una competencia, sin resultado
    public function findResultEmpty($idCompetencia, $fase)
    {
        $entityManager = $this->getEntityManager();
        // $query = $entityManager->createQuery(
        //     '   SELECT e
        //         FROM App\Entity\Encuentro e
        //         WHERE (e.competencia = :idCompetencia
        //         AND c.fase = :fase
        //         AND (e.rdo_comp1 is NULL OR e.rdo_comp2 is NULL))
        //     ')->setParameter('idCompetencia', $idCompetencia)
        //     ->setParameter('fase', $fase);
        $query = $entityManager->createQuery(
            '   SELECT e
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\Jornada j
                WITH e.jornada = j.id
                WHERE (e.competencia = :idCompetencia
                AND j.fase = :fase
                AND (e.rdo_comp1 is NULL OR e.rdo_comp2 is NULL))
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('fase', $fase);

        return $query->execute();
    }

    // Pre: solo para tipo grupos
    // Buscamos encuentros de una fase, de una competencia, sin resultado
    public function findResultEmptyCompGroup($idCompetencia, $fase, $grupo)
    {
        // TODO: modificar query
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT e
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\Jornada j
                WITH e.jornada = j.id
                WHERE (e.competencia = :idCompetencia
                AND e.grupo = :grupo
                AND j.fase = :fase
                AND (e.rdo_comp1 is NULL OR e.rdo_comp2 is NULL))
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('fase', $fase)
            ->setParameter('grupo', $grupo);

        return $query->execute();
    }

    // verificamos que la competencia cuente con encuentros
    public function confrontationCompetition($idCompetencia, $fase)
    {
        $entityManager = $this->getEntityManager();
        
        $query = $entityManager->createQuery(
            '   SELECT e
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\Jornada j
                WITH e.jornada = j.id
                WHERE (e.competencia = :idCompetencia AND j.fase = :fase)
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('fase', $fase);

        return $query->execute();
    }

}
