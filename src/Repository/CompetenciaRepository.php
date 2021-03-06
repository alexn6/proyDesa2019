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
use App\Entity\Ciudad;

use App\Utils\Constant;

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

    // recuperamos las fases con encuentros de la competencia
    public function phasesCreated($idCompetencia){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            ' SELECT DISTINCT j.fase
            FROM App\Entity\Jornada j 
            WHERE j.competencia = :idCompetencia
            ORDER BY j.fase DESC
            ')->setParameter('idCompetencia', $idCompetencia);

        return $query->execute();
    }

    // vemos si existe un competidor libre en la jornada
    public function getAliasCompetitors($idCompetencia){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            ' SELECT uc.alias
            FROM App\Entity\UsuarioCompetencia uc
            INNER JOIN App\Entity\Rol r
            WITH (uc.rol = r.id AND r.nombre = :rolCompetidor)
            WHERE uc.competencia = :idCompetencia
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('rolCompetidor', Constant::ROL_COMPETIDOR);
        $resultQuery = $query->execute();

        // pasamos los ids a un array
        $arrayAlias = array();
        for ($i=0; $i < count($resultQuery) ; $i++) { 
            array_push($arrayAlias, $resultQuery[$i]['alias']);
        }

        return $arrayAlias;
    }

    // devuelve los alias de los competidores de un grupo de una competencia
    public function getAliasCompetitorsByGroup($idCompetencia, $grupo){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            ' SELECT DISTINCT e.id
            FROM App\Entity\Encuentro e
            WHERE (e.competencia = :idCompetencia AND e.grupo = :grupo)
            ')->setParameter('idCompetencia', $idCompetencia)
            ->setParameter('grupo', $grupo);
        
        $idEnc = $query->execute();
        // var_dump($idEnc);
        // pasamos los ids a un array
        $arrayIdEnc = array();
        for ($i=0; $i < count($idEnc) ; $i++) { 
            array_push($arrayIdEnc, $idEnc[$i]['id']);
        }
        // los pasamos a string para incorporarlo a la query
        $arrayIdEnc = implode(", ", $arrayIdEnc);
        $stringIdEncuentros = "(".$arrayIdEnc.")";
        // var_dump($arrayIdComp1);

        // recuperamos los id de competidor1 de todos los encuentros del grupo
        $queryComp1 = $entityManager->createQuery(
            ' SELECT DISTINCT uc.id, uc.alias
            FROM App\Entity\Encuentro e
            INNER JOIN App\Entity\UsuarioCompetencia uc
            WITH (e.competidor1 = uc.id
            AND e.competencia = :idCompetencia AND e.id IN '.$stringIdEncuentros.')
            ')->setParameter('idCompetencia', $idCompetencia);
        $idComp1 = $queryComp1->execute();

        // recuperamos los id de competidor1 de todos los encuentros del grupo
        $queryComp2 = $entityManager->createQuery(
            ' SELECT DISTINCT uc.id, uc.alias
            FROM App\Entity\Encuentro e
            INNER JOIN App\Entity\UsuarioCompetencia uc
            WITH (e.competidor2 = uc.id
            AND e.competencia = :idCompetencia AND e.id IN '.$stringIdEncuentros.')
            ')->setParameter('idCompetencia', $idCompetencia);
        $idComp2 = $queryComp2->execute();

        $idCompetidoresUnion = array_merge($idComp1, $idComp2);
        $aliasCompetitorsGroup = array();
        for ($i=0; $i < count($idCompetidoresUnion) ; $i++) {
            $idCompetidor = $idCompetidoresUnion[$i]['alias'];
            if(!in_array( $idCompetidor, $aliasCompetitorsGroup)){
                array_push($aliasCompetitorsGroup, $idCompetidoresUnion[$i]['alias']);
            }
        }
        //var_dump($aliasCompetitorsGroup);

        return $aliasCompetitorsGroup;
    }

    // recupera todos los datos de la competencia  como string, agregado el rol
    public function dataOffline($idUsuario, $idCompetencia){
        $entityManager = $this->getEntityManager();
        
        $query = ' SELECT c.id, c.nombre, cat.nombre categoria, org.nombre organizacion, c.genero, c.estado, c.frec_dias, ciu.nombre as ciudad, c.fecha_ini, r.nombre as rol, c.fase_actual
            FROM App\Entity\Competencia c
            INNER JOIN App\Entity\Ciudad ciu
            WITH c.ciudad = ciu.id
            INNER JOIN App\Entity\Categoria cat
            WITH c.categoria = cat.id
            INNER JOIN App\Entity\TipoOrganizacion org
            WITH c.organizacion = org.id
            INNER JOIN App\Entity\UsuarioCompetencia uc
            WITH c.id = uc.competencia
            INNER JOIN App\Entity\Rol r
            WITH uc.rol = r.id
            AND uc.usuario = :idUsuario
            AND c.id = :idCompetencia';

        $query = $entityManager->createQuery($query);
        // le seteamos el parametro
        $query->setParameter('idUsuario',$idUsuario);
        $query->setParameter('idCompetencia',$idCompetencia);

        // agrupamos los roles del usuario por cada competencia
        $competitions = $this->joinRolCompetitions($query->execute());

        return $competitions;
    }

    // Precondicion: idUsuario obligatorio
    // Filtro de competencias x usuario con rol
    public function filterCompetitionsByUserFull($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad, $estado){
        
        // vamos en busca de las competencias con rol
        $competitionsRol = $this->filterCompetitionsRol($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad, $estado);
        // ahora vamos a buscar las competencias en las que no tiene un rol asignado
        $competitionsUnrol = $this->filterCompetitionsUnrol($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad, $estado);
        // juntamos los array obtenidos
        $competitions = array_merge($competitionsRol, $competitionsUnrol);

        //var_dump($competitions);

        // ordenamos las competencias por id
        usort($competitions, function($a, $b) {
                                return strnatcmp($a['id'], $b['id']);
                            }
            );

        // agrupamos los roles del usuario por cada competencia
        $competitions = $this->joinRolCompetitions($competitions);

        return $competitions;
    }

    // ##############################################################################
    // ############################ Funciones auxiliares ############################

    // filtro de competencias con roles de un usuario
    // 1ra parte: las competencias en las que si tengo un rol
    public function filterCompetitionsRol($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad, $estado){
        $entityManager = $this->getEntityManager();

        $queryBase = ' SELECT c.id, c.fecha_ini, c.nombre, c.frec_dias as frecuencia, categ.nombre categoria, organ.nombre tipo_organizacion, c.genero, c.fase_actual, c.estado, ciu.nombre as ciudad, r.nombre as rol
                        FROM App\Entity\Competencia c
                        INNER JOIN App\Entity\Ciudad ciu
                        WITH c.ciudad = ciu.id
                        INNER JOIN App\Entity\Categoria categ
                        WITH c.categoria = categ.id
                        INNER JOIN App\Entity\TipoOrganizacion organ
                        WITH c.organizacion = organ.id
                        INNER JOIN App\Entity\UsuarioCompetencia uc
                        WITH c.id = uc.competencia
                        INNER JOIN App\Entity\Rol r
                        WITH uc.rol = r.id
                        WHERE uc.usuario = :idUsuario';

        // le agregamos los filtros a la query
        $queryBase = $this->addFilters($queryBase, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad, $estado);

        // agregamos el order by
        $queryBase = $queryBase.' ORDER BY c.id ASC';

        $query = $entityManager->createQuery($queryBase);
        // le seteamos el parametro
        $query->setParameter('idUsuario',$idUsuario);
            
        return $query->execute();
    }
            
    // filtro de competencias con roles de un usuario, NULL si no las hay
    // 2da parte: las competencias en las que no tengo un rol
    public function filterCompetitionsUnrol($idUsuario, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad, $estado){

        $entityManager = $this->getEntityManager();

        // recuperamos las competencias en las que el usuario cuenta con un rol
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
        //var_dump(count($resultQuery));
        if(count($resultQuery) == 0){
            $hayResultados = false;
        }

        // base de la query
        $queryBase = ' SELECT DISTINCT c.id, c.fecha_ini, c.nombre, c.frec_dias as frecuencia, categ.nombre categoria, organ.nombre tipo_organizacion, c.genero, c.fase_actual, c.estado, ciu.nombre as ciudad, \'ESPECTADOR\' as rol
                        FROM App\Entity\Competencia c
                        INNER JOIN App\Entity\Ciudad ciu
                        WITH c.ciudad = ciu.id
                        INNER JOIN App\Entity\Categoria categ
                        WITH c.categoria = categ.id
                        INNER JOIN App\Entity\TipoOrganizacion organ
                        WITH c.organizacion = organ.id
                        ';
        
        $array_idCompetencias = array();
        $stringQueryWhere;
        
        // le incorporamos los filtros a la query
        $queryBase = $this->addFilters($queryBase, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad, $estado);
        
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
        //var_dump($queryBase);
        $query = $entityManager->createQuery($queryBase);
            
        return $query->execute();
    }

    // #####################################################################################
    // ########################## funciones privadas #######################################
    // incorporamos los filtros a las queries
    private function addFilters($stringQueryBase, $nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad, $estado){
        $qb = $this->getEntityManager()->createQueryBuilder();
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
        if($estado != NULL){
            // creamos la parte de la consulta con el parametro recibido y la juntamos
            $stringQueryEstado = ' AND c.estado = '.$estado;
            $stringQueryBase = $stringQueryBase.$stringQueryEstado;
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
            $stringQueryCiudad = ' AND ciu.nombre LIKE '.$like;
            $stringQueryBase = $stringQueryBase.$stringQueryCiudad;
        }

        return $stringQueryBase;
    }

    // recibe las competencias con un rol en cada fila y las agrupa por competencia
    // y conjunto de roles
    private function joinRolCompetitions($competitions){
        $competitionsMin = array();
        // controlamos que existan elementos en el array
        if(count($competitions) == 0){
            return null;
        }

        // tomamos el primer valor y pasamos el rol a un array de roles
        $competitionAux = $competitions[0];
        $roles = array();
        // guardamos el primer rol
        array_push($roles, $competitionAux['rol']);

        // si existe sola una competencia con un rol devolvemos la competencia
        if(count($competitions) == 1){
            $competitionAux['rol'] = $roles;
            array_push($competitionsMin, $competitionAux);
            return $competitionsMin;
        }

        // bandera para guardar el ultimo
        $guardarUltimo = false;

        // recorremos las demas filas de competencias con roles
        for ($i=1; $i < count($competitions); $i++) {
            $competitionActual = $competitions[$i];
            // si se trata de la misma competencia agregamos el nuevo rol
            if($competitionAux['id'] == $competitionActual['id']){
                $nuevoRol = $competitionActual['rol'];
                if(!in_array($nuevoRol, $roles)){
                    array_push($roles, $nuevoRol);
                }
                $guardarUltimo = false;
            }
            // si no es la misma competencia tenemos que guardar el cjto de roles
            // y descartar las competencias repetidas
            else{
                $competitionAux['rol'] = $roles;
                array_push($competitionsMin, $competitionAux);
                // reseteamos la lista de roles
                $roles = array();
                $competitionAux = $competitionActual;
                array_push($roles, $competitionAux['rol']);
                $guardarUltimo = true;
            }
        }

        // faltaria comparar o guardar el ultimo valor del array
        if($guardarUltimo){
            $cantCompetitions = count($competitions);
            $competitionAux = $competitions[$cantCompetitions - 1];
            $roles = array();
            // guardamos el rol
            array_push($roles, $competitionAux['rol']);
            // guardamos los roles como un array
            $competitionAux['rol'] = $roles;
            array_push($competitionsMin, $competitionAux);
        }
        else{
            // esto por si sale del for y no xq cambia el id de la competencia, sino xq es la ultima competencia
            if(count($roles) > 0){
                // guardamos los roles como un array
                $competitionAux['rol'] = $roles;
                array_push($competitionsMin, $competitionAux);
            }
        }
        
        return $competitionsMin;
    }


    /**
     * @return Competencia[] Returns an array of Competencia objects
     */
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

}
