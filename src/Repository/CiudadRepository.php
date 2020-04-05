<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Ciudad;

/**
 * @method Ciudad|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ciudad|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ciudad[]    findAll()
 * @method Ciudad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiudadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ciudad::class);
    }
    
    //funcion busqueda por nombre
    public function findCitiesByName($nombreCiudad){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            ' SELECT c
            FROM App\Entity\Ciudad c 
            WHERE c.nombre LIKE :nombreCiudad
            ')->setParameter('nombreCiudad','%'.$nombreCiudad.'%');
        return $query->execute();
    }
}
