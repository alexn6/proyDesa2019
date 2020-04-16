<?php

namespace App\Repository;

use App\Entity\Edicion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Edicion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Edicion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Edicion[]    findAll()
 * @method Edicion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EdicionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Edicion::class);
    }

    // /**
    //  * @return Edicion[] Returns an array of Edicion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Edicion
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // recupera las ediciones de un encuentro ordenadas segun su fecha de edicion
    public function getEditionsByConfrontation($idEncuentro){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            '   SELECT ed.operacion, ed.editor, ed.fecha
                FROM App\Entity\Edicion ed
                INNER JOIN App\Entity\Encuentro en
                WITH ed.encuentro = en.id
                WHERE en.id = :idEncuentro
                ORDER BY ed.fecha DESC
            ')->setParameter('idEncuentro',$idEncuentro);
        
        return $query->execute();
    }
}
