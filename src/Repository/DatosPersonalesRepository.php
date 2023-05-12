<?php

namespace App\Repository;

use App\Entity\DatosPersonales;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DatosPersonales>
 *
 * @method DatosPersonales|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatosPersonales|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatosPersonales[]    findAll()
 * @method DatosPersonales[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatosPersonalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatosPersonales::class);
    }

    public function save(DatosPersonales $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DatosPersonales $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DatosPersonales[] Returns an array of DatosPersonales objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DatosPersonales
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
