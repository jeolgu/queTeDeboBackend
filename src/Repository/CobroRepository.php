<?php

namespace App\Repository;

use App\Entity\Cobro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cobro>
 *
 * @method Cobro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cobro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cobro[]    findAll()
 * @method Cobro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CobroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cobro::class);
    }

    public function save(Cobro $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cobro $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getArchivados($value, $limite =""): ?array{

        $sql = $this->createQueryBuilder('cobro')
            ->andWhere("cobro.creador = :val AND cobro.archivado = true")
            ->setParameter("val", $value)
            ->orderBy("cobro.creacion", "DESC");

        if($limite) $sql->setMaxResults($limite);
        
        return $sql->getQuery()
            ->getResult();
    }

    public function getEnviados($value, $limite = ""): ?array
    {

        $sql = $this->createQueryBuilder("cobro")
            ->andWhere("cobro.creador = :val AND cobro.completado = false")
            ->setParameter("val", $value)
            ->OrderBy("cobro.creacion", "DESC");
        
            //if($limite) $sql->setMaxResults($limite);

            return $sql->getQuery()
                    ->getResult();

    }

    public function getRecibidos($value, $limite = ""): ?array
    {

        $sql = $this->createQueryBuilder("cobro")
            ->andWhere("cobro.receptor = :val AND cobro.completado = false")
            ->setParameter("val", $value)
            ->OrderBy("cobro.creacion", "DESC");
        
            if($limite) $sql->setMaxResults($limite);

            return $sql->getQuery()
                    ->getResult();

    }


    public function getHistoricoEnviados($value, $limite = ""): ?array
    {

        $sql = $this->createQueryBuilder("cobro")
            ->andWhere("cobro.creador = :val AND cobro.completado = true AND cobro.revisado = true")
            ->setParameter("val", $value)
            ->OrderBy("cobro.fecha_completado", "DESC");
        
            if($limite) $sql->setMaxResults($limite);

            return $sql->getQuery()
                    ->getResult();

    }

    public function getHistoricoRecibidos($value, $limite = ""): ?array
    {

        $sql = $this->createQueryBuilder("cobro")
            ->andWhere("cobro.receptor = :val AND cobro.completado = true AND cobro.revisado = true")
            ->setParameter("val", $value)
            ->OrderBy("cobro.fecha_completado", "DESC");
        
            if($limite) $sql->setMaxResults($limite);

            return $sql->getQuery()
                    ->getResult();
    }

//    /**
//     * @return Cobro[] Returns an array of Cobro objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cobro
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
