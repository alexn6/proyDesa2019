<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Competencia;
use App\Entity\TipoOrganizacion;

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

    //funcion busqueda por nombre
    public function filterCompetitions($nombreCompetencia, $idCategoria, $idDeporte, $idTipoorg, $genero, $ciudad){
        $entityManager = $this->getEntityManager();
        $qb = $this->getEntityManager()->createQueryBuilder();
        // partimos de una consulta base para 
        $stringQueryBase = 'SELECT c.nombre, categ.nombre categoria, organ.nombre tipo_organizacion, c.ciudad, c.genero
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
    private function concatStringQuery(){
        
    }
}
