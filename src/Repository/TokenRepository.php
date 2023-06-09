<?php

namespace App\Repository;

use App\Entity\Token;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Token>
 *
 * @method Token|null find($id, $lockMode = null, $lockVersion = null)
 * @method Token|null findOneBy(array $criteria, array $orderBy = null)
 * @method Token[]    findAll()
 * @method Token[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    public function save(Token $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Token $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function dameTokenPorIdUsuario($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id_usuario = :val AND :hoy <= t.expiracion')
            ->setParameter('val', $value)
            ->setParameter('hoy', new DateTime())
            ->orderBy('t.expiracion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function compruebaToken($token): bool{

        $sql = $this->createQueryBuilder('t')
            ->andWhere('t.token = :val AND :hoy <= t.expiracion')
            ->setParameter('val', $token)
            ->setParameter('hoy', new DateTime())
            ->orderBy('t.expiracion', 'DESC')
            ->getQuery()
            ->getResult();

        return (count($sql)>0) ? true: false;
    }

    public function dameUsuarioPorToken($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.token = :val AND :hoy <= t.expiracion')
            ->setParameter('val', $value)
            ->setParameter('hoy', new DateTime())
            ->orderBy('t.expiracion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Token[] Returns an array of Token objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Token
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
