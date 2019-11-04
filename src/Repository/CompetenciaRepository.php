<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Competencia;
use App\Entity\TipoOrganizacion;
use App\Entity\UsuarioCompetencia;
use App\Entity\Rol;
use App\Entity\Deporte;
use App\Entity\Categoria;

/**
 * @method Competencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competencia[]    findAll()
 * @method Competencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competencia::class);
    }

    //funcion busqueda por nombre
    public function findCompetitionsByName($nameCompetition){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            ' SELECT c
            FROM App\Entity\Competencia c 
            WHERE c.nombre LIKE :nameCompetition
            ')->setParameter('nameCompetition','%'.$nameCompetition.'%');

            
        return $query->execute();
    }

    // filtro de competencias
    public function filterCompetitions($nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad){
        $entityManager = $this->getEntityManager();
        $qb = $this->getEntityManager()->createQueryBuilder();
        // partimos de una consulta base para 
        $stringQueryBase = 'SELECT c.id, c.nombre, categ.nombre categoria, organ.nombre tipo_organizacion, c.ciudad, c.genero
                            FROM App\Entity\Competencia c
                            INNER JOIN App\Entity\Categoria categ
                            WITH c.categoria = categ.id
                            INNER JOIN App\Entity\TipoOrganizacion organ
                            WITH c.organizacion = organ.id';

        // ############# primero trabajamos con los join #############
        // si recibimos parametros agrandamos la consulta
        if($idCategoria != NULL){
            // creamos la parte de la consulta con el parametro recibido
            $stringQueryCategoria = ' AND c.categoria = '.$idCategoria;
            // juntamos la consulta en una sola
            $stringQueryBase = $stringQueryBase.$stringQueryCategoria;
        }
        // si recibimos parametros agrandamos la consulta
        if($idDeporte != NULL){
            // creamos la parte de la consulta con el parametro recibido
            $stringQueryDeporte = ' AND categ.deporte = '.$idDeporte;
            // juntamos la consulta en una sola
            $stringQueryBase = $stringQueryBase.$stringQueryDeporte;
        }
        // si recibimos parametros agrandamos la consulta
        if($idTipoorg != NULL){
            // creamos la parte de la consulta con el parametro recibido
            $stringQueryTipoorg = ' AND c.organizacion = '.$idTipoorg;
            // juntamos la consulta en una sola
            $stringQueryBase = $stringQueryBase.$stringQueryTipoorg;
        }
        // ############# ahora trabajamos con los datos de las columnas #############
        // si recibimos parametros agrandamos la consulta
        if($genero != NULL){
            // creamos la parte de la consulta con el parametro recibido
            $stringQueryGenero = ' AND c.genero = '.$genero;
            // juntamos la consulta en una sola
            $stringQueryBase = $stringQueryBase.$stringQueryGenero;
        }

        // vemos si recibimos un nombre de competencia como parametro
        if($nombreCompetencia != NULL){
            //var_dump($nombreCompetencia);
            // escapamos los %, no los toma como debe si no hacemos esto
            // ("u.roles LIKE '%$role%'") => podria ser una mejor solucion pero
            // deberiamos cambiar la consulta por "" y probar que todo anda como debe
            $like = $qb->expr()->literal('%'.$nombreCompetencia.'%');
            $stringQueryNombreComp = ' AND c.nombre LIKE '.$like;
            // juntamos la consulta en una sola
            $stringQueryBase = $stringQueryBase.$stringQueryNombreComp;
        }

        // vemos si recibimos un nombre de competencia como parametro
        if($ciudad != NULL){
            // escapamos los %, no los toma como debe si no hacemos esto
            // ("u.roles LIKE '%$role%'") => podria ser una mejor solucion pero
            // deberiamos cambiar la consulta por "" y probar que todo anda como debe
            $like = $qb->expr()->literal('%'.$ciudad.'%');
            $stringQueryCiudad = ' AND c.ciudad LIKE '.$like;
            // juntamos la consulta en una sola
            $stringQueryBase = $stringQueryBase.$stringQueryCiudad;
        }

        $query = $entityManager->createQuery($stringQueryBase);
            
        return $query->execute();
    }

    // filtro de competencias con roles de un usuario
    // 1ra parte: las competencias en las que si tengo un rol
    // public function filterCompetitionsRol($nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad){
    public function filterCompetitionsRol($idUsuario){
        $entityManager = $this->getEntityManager();
        // $query = $entityManager->createQuery(
        //     ' SELECT c.id, c.nombre, c.genero, r.nombre as rol
        //     FROM App\Entity\Competencia c 
        //     INNER JOIN App\Entity\UsuarioCompetencia uc
        //     WITH c.id = uc.competencia
        //     INNER JOIN App\Entity\Rol r
        //     WITH uc.rol = r.id
        //     WHERE uc.usuario = :idUsuario
        //     ORDER BY c.id ASC
        //     ')->setParameter('idUsuario',$idUsuario);

        $query = $entityManager->createQuery(
            // c.ciudad, c.genero
            ' SELECT c.id, c.nombre, categ.nombre categoria, organ.nombre tipo_organizacion, c.genero, c.ciudad, r.nombre as rol
            FROM App\Entity\Competencia c
            INNER JOIN App\Entity\Categoria categ
            WITH c.categoria = categ.id
            INNER JOIN App\Entity\TipoOrganizacion organ
            WITH c.organizacion = organ.id
            INNER JOIN App\Entity\UsuarioCompetencia uc
            WITH c.id = uc.competencia
            INNER JOIN App\Entity\Rol r
            WITH uc.rol = r.id
            WHERE uc.usuario = :idUsuario
            ORDER BY c.id ASC
        ')->setParameter('idUsuario',$idUsuario);

            
        return $query->execute();
    }
            
    // filtro de competencias con roles de un usuario, NULL si no las hay
    // 2da parte: las competencias en las que no tengo un rol
    // public function filterCompetitionsRol($nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad){
    public function filterCompetitionsUnrol($idUsuario){

        $entityManager = $this->getEntityManager();

        // recuperamos las competencias en las que el usaurio cuenta con un rol
        $subQuery = $entityManager->createQuery(
            ' SELECT DISTINCT c.id
            FROM App\Entity\Competencia c 
            INNER JOIN App\Entity\UsuarioCompetencia uc
            WITH c.id = uc.competencia
            WHERE uc.usuario = :idUsuario
            ORDER BY c.id ASC
            ')->setParameter('idUsuario',$idUsuario);

        $hayResultados = true;
        $resultQuery = $subQuery->execute();
        if(count($resultQuery) == 0){
            $hayResultados = false;
        }

        // base de la query
        $queryBase = ' SELECT DISTINCT c.id, c.nombre, c.genero, \'ESPECTADOR\' as rol
                        FROM App\Entity\Competencia c 
                        INNER JOIN App\Entity\UsuarioCompetencia uc
                        WITH c.id = uc.competencia
                        ';
        
        $array_idCompetencias = array();
        $stringQueryWhere;
        if($hayResultados){
            // pasamos solo los id de las competencias a un array
            foreach ($resultQuery as &$valor) {
                array_push($array_idCompetencias, $valor['id']);
            }
            // los pasamos a string para incorporarlo a la query
            $array_idCompetencias = implode(", ", $array_idCompetencias);
            $stringIdCompetencias = "(".$array_idCompetencias.")";
            $stringQueryWhere = ' WHERE c.id NOT IN '.$stringIdCompetencias;

            $queryBase = $queryBase.$stringQueryWhere;
        }
        
        // agregamos el order by
        $queryBase = $queryBase.' ORDER BY c.id ASC';

        // $query = $entityManager->createQuery(
        //     ' SELECT DISTINCT c.id, c.nombre, c.genero, \'ESPECTADOR\' as rol
        //     FROM App\Entity\Competencia c 
        //     INNER JOIN App\Entity\UsuarioCompetencia uc
        //     WITH c.id = uc.competencia
        //     WHERE c.id NOT IN '.$stringIdCompetencias.'
        //     ORDER BY c.id ASC
        //     ');

        $query = $entityManager->createQuery($queryBase);
            
        return $query->execute();
    }
    
    // Precondicion: idUsuario obligatorio
    // Filtro de competencias x usuario con rol
    public function filterCompetitionsByUser($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad){
        $entityManager = $this->getEntityManager();
        $qb = $this->getEntityManager()->createQueryBuilder();
        // partimos de una consulta base para 
        $stringQueryBase = 'SELECT c.id, c.nombre, categ.nombre categoria, organ.nombre tipo_organizacion, c.ciudad, c.genero
                            FROM App\Entity\Competencia c
                            INNER JOIN App\Entity\Categoria categ
                            WITH c.categoria = categ.id
                            INNER JOIN App\Entity\TipoOrganizacion organ
                            WITH c.organizacion = organ.id';

        // ############# primero trabajamos con los join #############
        // si recibimos parametros agrandamos la consulta
        if($idCategoria != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryCategoria = ' AND c.categoria = '.$idCategoria;
            $stringQueryBase = $stringQueryBase.$stringQueryCategoria;
        }
        // si recibimos parametros agrandamos la consulta
        if($idDeporte != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryDeporte = ' AND categ.deporte = '.$idDeporte;
            $stringQueryBase = $stringQueryBase.$stringQueryDeporte;
        }
        // si recibimos parametros agrandamos la consulta
        if($idTipoorg != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryTipoorg = ' AND c.organizacion = '.$idTipoorg;
            $stringQueryBase = $stringQueryBase.$stringQueryTipoorg;
        }
        // ############# ahora trabajamos con los datos de las columnas #############
        // si recibimos parametros agrandamos la consulta
        if($genero != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryGenero = ' AND c.genero = '.$genero;
            $stringQueryBase = $stringQueryBase.$stringQueryGenero;
        }

        // vemos si recibimos un nombre de competencia como parametro
        if($nombreCompetencia != NULL){
            // escapamos los %, no los toma como debe si no hacemos esto
            $like = $qb->expr()->literal('%'.$nombreCompetencia.'%');
            $stringQueryNombreComp = ' AND c.nombre LIKE '.$like;
            $stringQueryBase = $stringQueryBase.$stringQueryNombreComp;
        }

        // vemos si recibimos un nombre de competencia como parametro
        if($ciudad != NULL){
            // escapamos los %, no los toma como debe si no hacemos esto
            $like = $qb->expr()->literal('%'.$ciudad.'%');
            $stringQueryCiudad = ' AND c.ciudad LIKE '.$like;
            $stringQueryBase = $stringQueryBase.$stringQueryCiudad;
        }

        $query = $entityManager->createQuery($stringQueryBase);
            
        return $query->execute();
    }

    // /**
    //  * @return Competencia[] Returns an array of Competencia objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Competencia
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // ##########################################################################
    // ########################## funciones auxiliares ##########################

}
