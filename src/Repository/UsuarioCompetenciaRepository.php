<?php

namespace App\Repository;

use App\Entity\UsuarioCompetencia;
use App\Entity\Usuario;
use App\Entity\Competencia;
use App\Entity\Rol;

use App\Utils\Constant;

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

   // ######################################################################
   // ######################## USUARIOS ROL SEGUN COMPETENCIA ##############################

   // recuperamos los usuarios solicitantes de una competencia
  public function findSolicitantesByCompetencia($idCompetencia)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
        '   SELECT u.id, u.nombreUsuario, u.nombre, u.apellido, u.correo, uc.alias alias
            FROM App\Entity\UsuarioCompetencia uc
            INNER JOIN App\Entity\Usuario u
            WITH uc.usuario = u.id
            INNER JOIN App\Entity\Rol r
            WITH uc.rol = r.id
            WHERE uc.competencia = :idCompetencia
            AND (r.nombre = :rol)
        ')->setParameter('rol', Constant::ROL_SOLICITANTE)
        ->setParameter('idCompetencia', $idCompetencia);

      return $query->execute();
  }
   // controlamos que el alias no se encuetre usado por otro usuario en la competencia
   public function comprobarAlias($idCompetencia, $alias)
   {
       $entityManager = $this->getEntityManager();
       $query = $entityManager->createQuery(
         '   SELECT uc
             FROM App\Entity\UsuarioCompetencia uc
             INNER JOIN App\Entity\Usuario u
             WITH uc.usuario = u.id
             WHERE uc.competencia = :idCompetencia
             AND uc.alias = :alias
         ')->setParameter('alias', $alias)
         ->setParameter('idCompetencia', $idCompetencia);
 
       return $query->execute();
   }
 
  // recuperamos los usuarios competidortes de una competencia
  public function findCompetidoresByCompetencia($idCompetencia)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT u.id, u.nombreUsuario, u.nombre, u.apellido, u.correo, uc.alias
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Usuario u
              WITH uc.usuario = u.id
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              AND r.nombre = :rol
              AND uc.competencia = :idCompetencia
          ')->setParameter('rol', Constant::ROL_COMPETIDOR)
          ->setParameter('idCompetencia', $idCompetencia);    

      return $query->execute();
  }

  // recuperamos los usuarios competidortes de una competencia
  public function countCompetidoresByCompetencia($idCompetencia)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT COUNT(u.id)
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Usuario u
              WITH uc.usuario = u.id
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              AND r.nombre = :rol
              AND uc.competencia = :idCompetencia
          ')->setParameter('rol', Constant::ROL_COMPETIDOR)
          ->setParameter('idCompetencia', $idCompetencia);    

      return $query->execute();
  }

  // recuperamos el nombreusaurio de los competidortes de una competencia
  public function findNameCompetidoresByCompetencia($idCompetencia)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT u.nombreUsuario
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Usuario u
              WITH uc.usuario = u.id
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              AND r.nombre = :rol
              AND uc.competencia = :idCompetencia
          ')->setParameter('rol', Constant::ROL_COMPETIDOR)
          ->setParameter('idCompetencia', $idCompetencia);    

      return $query->execute();
  }

  // recuperamos el alias de los competidortes de una competencia
  public function findAliasCompetidoresByCompetencia($idCompetencia)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT uc.alias
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              AND r.nombre = :rol
              AND uc.competencia = :idCompetencia
          ')->setParameter('rol', Constant::ROL_COMPETIDOR)
          ->setParameter('idCompetencia', $idCompetencia);    

      return $query->execute();
  }

  // recuperamos los datos de los competidortes de una competencia para su trabajo offline
  public function competidoresByCompetenciaOffline($idCompetencia)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT uc.id, uc.alias, u.nombreUsuario, u.nombre, u.apellido, u.correo, c.id idCompetencia
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Competencia c
              WITH uc.competencia = c.id
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              INNER JOIN App\Entity\Usuario u
              WITH uc.usuario = u.id
              AND r.nombre = :rol
              AND uc.competencia = :idCompetencia
          ')->setParameter('rol', Constant::ROL_COMPETIDOR)
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
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              AND uc.competencia = :idCompetencia
              AND (r.nombre = :rol1 OR r.nombre = :rol2)
          ')->setParameter('rol1', Constant::ROL_ORGANIZADOR)
          ->setParameter('rol2', Constant::ROL_COORGANIZADOR)
          ->setParameter('idCompetencia', $idCompetencia);    

      return $query->execute();
  }

  // recuperamos el tokenFirebase del organizador de una competencia
  public function findOrganizatorCompetencia($idCompetencia)
  {
      $entityManager = $this->getEntityManager();

      $query = $entityManager->createQuery(
          '   SELECT u.token
              FROM App\Entity\UsuarioCompetencia uc
              INNER JOIN App\Entity\Usuario u
              WITH uc.usuario = u.id
              INNER JOIN App\Entity\Rol r
              WITH uc.rol = r.id
              AND uc.competencia = :idCompetencia
              AND r.nombre = :rol
          ')->setParameter('rol', Constant::ROL_ORGANIZADOR)
          ->setParameter('idCompetencia', $idCompetencia);

      return $query->execute();
  }

  // recuperamos los nombres de las competencias que SIGUE o PARTICIPA un usuario
  public function namesCompetitionsParticipe($idUsuario)
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
              AND (r.nombre = :rol1 OR r.nombre = :rol2)
          ')->setParameter('rol1', Constant::ROL_SEGUIDOR)
          ->setParameter('rol2', Constant::ROL_COMPETIDOR)
          ->setParameter('idUsuario', $idUsuario);    

      return $query->execute();
  }

  // ######################################################################
  // #################### COMPETENCIA SEGUN ROL USUARIO ##################

  // recuperamos las competencias de un usuario
  public function findCompetitionsByUser($idUsuario)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT c.id,c.nombre, cat.nombre categoria, d.nombre deporte, org.nombre tipo_organizacion, c.ciudad, c.genero, r.nombre rol
              FROM App\Entity\Competencia c
              INNER JOIN App\Entity\UsuarioCompetencia uc
              WITH uc.competencia = c.id
              INNER JOIN App\Entity\Categoria cat
              WITH c.categoria = cat.id
              INNER JOIN App\Entity\TipoOrganizacion org
              WITH c.organizacion = org.id
              INNER JOIN App\Entity\Deporte d
              WITH cat.deporte = d.id
              INNER JOIN App\Entity\Rol r
             WITH uc.rol = r.id
              AND uc.usuario = :idUsuario
          ')->setParameter('idUsuario', $idUsuario);


      return $query->execute();
  }

  // recuperamos las competencias seguidas por un usuario
  public function findCompetitionsFollowByUser($idUsuario)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
          '   SELECT c.id,c.nombre, cat.nombre categoria, d.nombre deporte, org.nombre tipo_organizacion, c.ciudad, c.genero, c.fase_actual, c.estado, r.nombre rol
              FROM App\Entity\Competencia c
              INNER JOIN App\Entity\UsuarioCompetencia uc
              WITH uc.competencia = c.id
              INNER JOIN App\Entity\Categoria cat
              WITH c.categoria = cat.id
              INNER JOIN App\Entity\TipoOrganizacion org
              WITH c.organizacion = org.id
              INNER JOIN App\Entity\Deporte d
             WITH cat.deporte = d.id
             INNER JOIN App\Entity\Rol r
             WITH uc.rol = r.id
              AND uc.usuario = :idUsuario
              AND r.nombre = :rolUser
          ')->setParameter('idUsuario', $idUsuario)
          ->setParameter('rolUser', Constant::ROL_SEGUIDOR);

      return $query->execute();
  }

  // recuperamos las competencias en las que participa un usuario
  public function findCompetitionsParticipatesByUser($idUsuario)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
        '   SELECT c.id,c.nombre, cat.nombre categoria, d.nombre deporte, org.nombre tipo_organizacion, c.ciudad, c.genero, c.fase_actual, c.estado, r.nombre rol
            FROM App\Entity\Competencia c
            INNER JOIN App\Entity\UsuarioCompetencia uc
            WITH uc.competencia = c.id
            INNER JOIN App\Entity\Categoria cat
            WITH c.categoria = cat.id
            INNER JOIN App\Entity\TipoOrganizacion org
            WITH c.organizacion = org.id
            INNER JOIN App\Entity\Deporte d
            WITH cat.deporte = d.id
            INNER JOIN App\Entity\Rol r
            WITH uc.rol = r.id
            AND uc.usuario = :idUsuario
            AND r.nombre = :rolUser
        ')->setParameter('idUsuario', $idUsuario)
        ->setParameter('rolUser', Constant::ROL_COMPETIDOR);


      return $query->execute();
  }

  // recuperamos las competencia que organiza un usuario
  public function findCompetitionsOrganizeByUser($idUsuario)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
       '   SELECT c.id, c.nombre, cat.nombre categoria, cat.duracion_default duracionDefault, d.nombre deporte, org.nombre tipo_organizacion, c.ciudad, c.genero, c.fase_actual, c.estado, r.nombre rol
           FROM App\Entity\Competencia c
           INNER JOIN App\Entity\UsuarioCompetencia uc
           WITH uc.competencia = c.id
           INNER JOIN App\Entity\Categoria cat
           WITH c.categoria = cat.id
           INNER JOIN App\Entity\TipoOrganizacion org
           WITH c.organizacion = org.id
           INNER JOIN App\Entity\Deporte d
           WITH cat.deporte = d.id
           INNER JOIN App\Entity\Rol r
           WITH uc.rol = r.id
           AND uc.usuario = :idUsuario
           AND (r.nombre = :rolUser OR r.nombre = :rolUser2)
       ')->setParameter('idUsuario', $idUsuario)
       ->setParameter('rolUser', Constant::ROL_ORGANIZADOR)
       ->setParameter('rolUser2', Constant::ROL_COORGANIZADOR);

      return $query->execute();
  }

  // recuperamos solicitudes de un usuario para ser co-organizador de competencias
 /* public function findCompetitionsRequestCoOrganizateByUser($idUsuario, $idCompetencia)
  {
      $entityManager = $this->getEntityManager();
      $query = $entityManager->createQuery(
        '   SELECT c.id,c.nombre, cat.nombre categoria, d.nombre deporte, org.nombre tipo_organizacion, c.ciudad, c.genero, r.nombre rol
            FROM App\Entity\Competencia c
            INNER JOIN App\Entity\UsuarioCompetencia uc
            WITH uc.competencia = c.id
            INNER JOIN App\Entity\Categoria cat
            WITH c.categoria = cat.id
            INNER JOIN App\Entity\TipoOrganizacion org
            WITH c.organizacion = org.id
            INNER JOIN App\Entity\Deporte d
            WITH cat.deporte = d.id
            INNER JOIN App\Entity\Rol r
            WITH uc.rol = r.id
            AND uc.usuario = :idUsuario
            AND r.nombre = :rolUser
            AND r.nombre = :rolUser2
        ')->setParameter('idUsuario', $idUsuario)
        ->setParameter('rolUser', Constant::ROL_ORGANIZADOR)
        ->setParameter('rolUser2', Constant::ROL_COORGANIZADOR);
      return $query->execute();
  }*/
  
}
