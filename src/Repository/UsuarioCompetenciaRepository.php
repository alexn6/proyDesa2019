<?php

namespace App\Repository;

use App\Entity\UsuarioCompetencia;
use App\Entity\Usuario;
use App\Entity\Competencia;

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
            ')->setParameter('rol', "PARTICIPANTE")
            ->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }

    // recuperamos los tokenFirebase de los organizadores de una competencia
    public function findOrganizatorsCompetencia($idCompetencia)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            '   SELECT u.token
                FROM App\Entity\UsuarioCompetencia uc
                INNER JOIN App\Entity\Usuario u
                WITH uc.usuario = u.id
                AND uc.competencia = :idCompetencia
                AND (uc.rol = :rol1 OR uc.rol = :rol2)
            ')->setParameter('rol1', "ORGANIZADOR")
            ->setParameter('rol2', "CO-ORGANIZADOR")
            ->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }

    // recuperamos las competencias de un usuario
    public function findCompetitionsByUser($idUsuario)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT c.nombre, cat.nombre categoria, org.nombre tipoorganizacion
                FROM App\Entity\Competencia c
                INNER JOIN App\Entity\UsuarioCompetencia uc
                WITH uc.competencia = c.id
                INNER JOIN App\Entity\Categoria cat
                WITH c.categoria = cat.id
                INNER JOIN App\Entity\TipoOrganizacion org
                WITH c.organizacion = org.id
                AND uc.usuario = :idUsuario
            ')->setParameter('idUsuario', $idUsuario);

        return $query->execute();
    }

    // recuperamos las competencias seguidas por un usuario
    public function findCompetitionsFollowByUser($idUsuario)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT c.nombre, cat.nombre categoria, org.nombre tipo_organizacion
                FROM App\Entity\Competencia c
                INNER JOIN App\Entity\UsuarioCompetencia uc
                WITH uc.competencia = c.id
                INNER JOIN App\Entity\Categoria cat
                WITH c.categoria = cat.id
                INNER JOIN App\Entity\TipoOrganizacion org
                WITH c.organizacion = org.id
                AND uc.usuario = :idUsuario
                AND uc.rol = :rolUser
            ')->setParameter('idUsuario', $idUsuario)
            ->setParameter('rolUser', "SEGUIDOR");

        return $query->execute();
    }

  // recuperamos las competencias en las que participa un usuario
  public function findCompetitionsParticipatesByUser($idUsuario)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT c.nombre, cat.nombre categoria, org.nombre tipo_organizacion
              FROM App\Entity\Competencia c
              INNER JOIN App\Entity\UsuarioCompetencia uc
              WITH uc.competencia = c.id
              INNER JOIN App\Entity\Categoria cat
              WITH c.categoria = cat.id
              INNER JOIN App\Entity\TipoOrganizacion org
              WITH c.organizacion = org.id
              AND uc.usuario = :idUsuario
              AND uc.rol = :rolUser
          ')->setParameter('idUsuario', $idUsuario)
          ->setParameter('rolUser', "PARTICIPANTE");

      return $query->execute();
  }

   // recuperamos el nombre de los usuarios de una competencia
   public function findCompetitionsOrganizeByUser($idUsuario)
   {
       $entityManager = $this->getEntityManager();
       $query = $entityManager->createQuery(
           '   SELECT c.nombre, cat.nombre categoria, org.nombre tipo_organizacion
               FROM App\Entity\Competencia c
               INNER JOIN App\Entity\UsuarioCompetencia uc
               WITH uc.competencia = c.id
               INNER JOIN App\Entity\Categoria cat
               WITH c.categoria = cat.id
               INNER JOIN App\Entity\TipoOrganizacion org
               WITH c.organizacion = org.id
               AND uc.usuario = :idUsuario
               AND uc.rol = :rolUser
           ')->setParameter('idUsuario', $idUsuario)
           ->setParameter('rolUser', "ORGANIZADOR");

       return $query->execute();
   }

   // ######################################################################
   // ############################## USUARIOS ##############################

   // recuperamos los usuarios solicitantes de una competencia
  public function findSolicitantesByCompetencia($idCompetencia)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT u.id, u.nombreUsuario, u.nombre, u.apellido, u.correo
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Usuario u
              WITH uc.usuario = u.id
              WHERE uc.competencia = :idCompetencia
              AND (uc.rol = :rol1 OR uc.rol = :rol2)
          ')->setParameter('rol1', "SOLICITANTE")
          ->setParameter('rol2', "SEG-SOLIC")
          ->setParameter('idCompetencia', $idCompetencia);

      return $query->execute();
  }

}
