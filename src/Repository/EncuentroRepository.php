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

    // recupera todos los encuentros de una competencia
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

    // recuperamos los encuentros de una competencia por determinada fase
    public function findEncuentrosLiga($idCompetencia, $jornada)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT e
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\Jornada j
                WITH e.jornada = j.id
                WHERE e.competencia = :idCompetencia
                AND j.numero = :jornada
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('jornada', $jornada);

        return $query->execute();
    }

    // recuperamos los encuentros de una competencia por determinada fase
    public function findEncuentrosEliminatoria($idCompetencia, $fase, $jornada)
    {
        $entityManager = $this->getEntityManager();
        $stringQuery =
            '   SELECT e
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\Jornada j
                WITH e.jornada = j.id
                WHERE e.competencia = :idCompetencia
                AND j.fase = :fase';
        if($jornada != null){
            $stringQuery = $stringQuery.' AND j.numero = :jornada';
        }

        $query = $entityManager->createQuery($stringQuery);
        // seteamos la competencia
        $query->setParameter('idCompetencia', $idCompetencia);
        $query->setParameter('fase', $fase);
        if($jornada != null){
            $query->setParameter('jornada', $jornada);
        }

        return $query->execute();
    }

    // recuperamos los encuentros de una competencia por jornada y grupo
    public function findEncuentrosFaseGrupos($idCompetencia, $jornada, $grupo)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT e
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\Jornada j
                WITH e.jornada = j.id
                WHERE e.competencia = :idCompetencia
                AND j.numero = :jornada
                AND e.grupo = :grupo
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('jornada', $jornada)
            ->setParameter('grupo', $grupo);

        return $query->execute();
    }

    // Pre: solo para tipo eliminatoria y liga
    // Buscamos encuentros de una fase, de una competencia, sin resultado
    public function findResultEmpty($idCompetencia, $fase)
    {
        $entityManager = $this->getEntityManager();
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

    // recuperamos la fase del encuentro
    public function getFase($idEncuentro){
        $entityManager = $this->getEntityManager();
        
        $query = $entityManager->createQuery(
            '   SELECT j.fase
                FROM App\Entity\Encuentro e
                INNER JOIN App\Entity\Jornada j
                WITH e.jornada = j.id
                WHERE e.id = :idEncuentro
            ')->setParameter('idEncuentro', $idEncuentro);

        return $query->execute();
    }

}
