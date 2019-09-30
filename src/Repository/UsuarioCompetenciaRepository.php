<?php

namespace App\Repository;

use App\Entity\UsuarioCompetencia;
use App\Entity\Usuario;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UsuarioCompetencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioCompetencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioCompetencia[]    findAll()
 * @method UsuarioCompetencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioCompetenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioCompetencia::class);
    }

    // /**
    //  * @return UsuarioCompetencia[] Returns an array of UsuarioCompetencia objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UsuarioCompetencia
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByCompetenciaJoinedToUsuario($idCompetencia)
    {
    return $this->createQueryBuilder('uc')
        // uc.usuario es la prop usuario dentro de UsuarioCompetencia
        ->innerJoin('uc.usuario', 'c')
        // selects all the category data to avoid the query
        ->addSelect('c')
        // ->andWhere('uc.rol = :idCompetencia')
        ->andWhere('uc.competencia = :idCompetencia')
        ->setParameter('idCompetencia', $idCompetencia)
        ->getQuery()
        ->getArrayResult();
    }

    // recuperamos el nombre de los usuarios de una competencia
    public function findParticipanteByCompetencia($idCompetencia)
    {

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT u.nombreUsuario
                FROM App\Entity\UsuarioCompetencia uc
                INNER JOIN App\Entity\Usuario u
                WITH uc.usuario = u.id
                WHERE uc.rol = :rol
                AND uc.competencia = :idCompetencia
            ')->setParameter('rol', "participante")
            ->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }
    
}
