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
    const COMPETIDOR1 = 1;
    const COMPETIDOR2 = 2;

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
            '   SELECT uc.id, uc.alias, r.jugados PJ, r.ganados PG, r.empatados PE, r.perdidos PP
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
        $userComp1 = $this->findCompetitorsNumberByGroup($idCompetencia, $grupo, 1);
        $userComp2 = $this->findCompetitorsNumberByGroup($idCompetencia, $grupo, 2);

        $userCompetitors = array_merge($userComp1, $userComp2);
        // eliminar los numeros repetidos
        $userCompetitors = array_unique($userCompetitors, SORT_REGULAR);

        $array_idCompetitors = array();
        // pasamos solo los id de las competencias a un array
        foreach ($userCompetitors as &$valor) {
            array_push($array_idCompetitors, $valor['id']);
        }
        // los pasamos a string para incorporarlo a la query
        $array_idCompetitors = implode(", ", $array_idCompetitors);
        $stringIdCompetitors = "(".$array_idCompetitors.")";

        $stringQuery =' SELECT DISTINCT uc.id, uc.alias, r.jugados PJ, r.ganados PG, r.empatados PE, r.perdidos PP, e.grupo
                             FROM App\Entity\UsuarioCompetencia uc
                             INNER JOIN App\Entity\Encuentro e
                             WITH e.competencia = uc.competencia
                             AND uc.competencia = :idCompetencia
                             AND e.grupo = :grupo
                             INNER JOIN App\Entity\Resultado r
                             WITH uc.id = r.competidor
                             AND uc.id IN '.$stringIdCompetitors.' ';

        // var_dump($stringQuery);
        
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery($stringQuery);
        
        // le seteamos los parametros
        $query->setParameter('idCompetencia',$idCompetencia);
        $query->setParameter('grupo',$grupo);

        return $query->execute();
    }

    // ################################################################################################
    // ################################ funciones auxiliares ################################
    
    // recuperamos los id de los competidores(comp1, comp2) segun demande
    private function findCompetitorsNumberByGroup($idCompetencia, $grupo, $nroCompetidor)
    {
        $competidor;
        if($nroCompetidor == $this::COMPETIDOR1){
            $competidor = "competidor1";
        }
        if($nroCompetidor == $this::COMPETIDOR2){
            $competidor = "competidor2";
        }

        $stringQuery =' SELECT uc.id
                        FROM App\Entity\Encuentro e
                        INNER JOIN App\Entity\UsuarioCompetencia uc ';
        $stringQuery = $stringQuery.' WITH e.'.$competidor.' = uc.id WHERE e.competencia = :idCompetencia AND e.grupo = :grupo';

        //var_dump($stringQuery);
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery($stringQuery);
        // le seteamos los parametros
        $query->setParameter('idCompetencia',$idCompetencia);
        $query->setParameter('grupo',$grupo);

        return $query->execute();
    }
}
