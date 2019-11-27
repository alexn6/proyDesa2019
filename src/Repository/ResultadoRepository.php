<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Resultado;
use App\Entity\UsuarioCompetencia;
use App\Entity\Encuentro;

/**
 * @method Resultado|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resultado|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resultado[]    findAll()
 * @method Resultado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resultado::class);
    }

    // /**
    //  * @return Resultado[] Returns an array of Resultado objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Resultado
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // recuperamos los datos de resultado de los competidores
    public function findResultCompetitors($idCompetencia)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT uc.alias, r.jugados PJ, r.ganados PG, r.empatados PE, r.perdidos PP
                FROM App\Entity\UsuarioCompetencia uc
                INNER JOIN App\Entity\Resultado r
                WITH uc.id = r.competidor
                WHERE uc.competencia = :idCompetencia
            ')->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }

    // recuperamos los datos de resultado de los competidores
    public function findCompetitorsByGroup($idCompetencia, $grupo)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT e.id
                FROM App\Entity\Encuentro e
                WHERE e.competencia = :idCompetencia
                AND e.grupo = :grupo
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('grupo', $grupo);

        return $query->execute();
    }

    // recuperamos los datos de resultado de los competidores por grupo
    public function findResultCompetitorsGroup($idCompetencia, $grupo)
    {
        $entityManager = $this->getEntityManager();
        // $query = $entityManager->createQuery(
        //      '   SELECT uc.alias, r.jugados PJ, r.ganados PG, r.empatados PE, r.perdidos PP
        //          FROM App\Entity\UsuarioCompetencia uc
        //          INNER JOIN App\Entity\Resultado r
        //          WITH uc.id = r.competidor
        //          INNER JOIN App\Entity\Encuentro e
        //          WITH e.competencia = uc.competencia
        //          AND e.grupo = :grupo
        //          WHERE uc.competencia = :idCompetencia
        //      ')->setParameter('idCompetencia', $idCompetencia)
        //      ->setParameter('grupo', $grupo);

        $resultQueryCompetitorByGroup = $this->findCompetitorsByGroup($idCompetencia, $grupo);
        $array_idCompetitors = array();
        // pasamos solo los id de las competencias a un array
        foreach ($resultQueryCompetitorByGroup as &$valor) {
            array_push($array_idCompetitors, $valor['id']);
        }
        // los pasamos a string para incorporarlo a la query
        $array_idCompetitors = implode(", ", $array_idCompetitors);
        $stringIdCompetitors = "(".$array_idCompetitors.")";

        $stringQuery =' SELECT DISTINCT uc.alias, r.jugados PJ, r.ganados PG, r.empatados PE, r.perdidos PP, e.grupo
                             FROM App\Entity\UsuarioCompetencia uc
                             INNER JOIN App\Entity\Encuentro e
                             WITH e.competencia = uc.competencia
                             AND uc.competencia = :idCompetencia
                             AND e.grupo = :grupo
                             INNER JOIN App\Entity\Resultado r
                             WITH uc.id = r.competidor
                             AND r.competidor IN (96)';

        var_dump($stringQuery);
        
        $query = $entityManager->createQuery($stringQuery);
        
        //  $query = $entityManager->createQuery(
        //       '   SELECT DISTINCT uc.alias, r.jugados PJ, r.ganados PG, r.empatados PE, r.perdidos PP, e.grupo
        //           FROM App\Entity\UsuarioCompetencia uc
        //           INNER JOIN App\Entity\Encuentro e
        //           WITH e.competencia = uc.competencia
        //           AND uc.competencia = :idCompetencia
        //           AND e.grupo = :grupo
        //           INNER JOIN App\Entity\Resultado r
        //           WITH uc.id = r.competidor
        //       ')->setParameter('idCompetencia', $idCompetencia)
        //      ->setParameter('grupo', $grupo);

        // $query = $entityManager->createQuery(
        //     '   SELECT uc.alias, r.jugados PJ, r.ganados PG, r.empatados PE, r.perdidos PP
        //         FROM App\Entity\UsuarioCompetencia uc
        //         INNER JOIN App\Entity\Resultado r
        //         WITH uc.id = r.competidor
        //         AND uc.competencia = :idCompetencia
        //         INNER JOIN App\Entity\Encuentro e
        //         WITH e.competencia = uc.competencia
        //         AND e.grupo = :grupo
        //     ')->setParameter('idCompetencia', $idCompetencia)
        //     ->setParameter('grupo', $grupo);
        // le seteamos los parametros
        $query->setParameter('idCompetencia',$idCompetencia);
        $query->setParameter('grupo',$grupo);

        return $query->execute();
    }
}
