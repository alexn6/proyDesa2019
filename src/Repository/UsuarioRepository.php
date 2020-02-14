<?php

namespace App\Repository;

use App\Entity\Usuario;
use App\Entity\Rol;
use App\Entity\Competencia;
use App\Entity\UsuarioCompetencia;

use App\Utils\Constant;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    // /**
    //  * @return Usuario[] Returns an array of Usuario objects
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
    public function findOneBySomeField($value): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getUsersByUsername($username){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
        '   SELECT u
            FROM App\Entity\Usuario u
            WHERE u.nombreUsuario LIKE :username
            ')->setParameter('username','%'.$username.'%');
        
        return $query->execute();
    }

    // recuperamos los nombres de las competencias que SIGUE un usuario
  public function namesCompetitionsFollow($idUsuario)
  {
      $entityManager = $this->getEntityManager();

      $query = $entityManager->createQuery(
          '   SELECT c.nombre
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Competencia c
              WITH uc.competencia = c.id
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              AND uc.usuario = :idUsuario
              AND r.nombre = :rol
          ')->setParameter('rol', Constant::ROL_SEGUIDOR)
          ->setParameter('idUsuario', $idUsuario);    

      return $query->execute();
  }

  // recuperamos los nombres de las competencias que COMPITE un usuario
  public function namesCompetitionsCompete($idUsuario)
  {
      $entityManager = $this->getEntityManager();

      $query = $entityManager->createQuery(
          '   SELECT c.nombre
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Competencia c
              WITH uc.competencia = c.id
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              AND uc.usuario = :idUsuario
              AND r.nombre = :rol
          ')->setParameter('rol', Constant::ROL_COMPETIDOR)
          ->setParameter('idUsuario', $idUsuario);    

      return $query->execute();
  }

}
