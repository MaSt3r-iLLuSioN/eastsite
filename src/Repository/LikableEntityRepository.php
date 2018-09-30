<?php

namespace App\Repository;

use App\Entity\LikableEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LikableEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method LikableEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method LikableEntity[]    findAll()
 * @method LikableEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikableEntityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LikableEntity::class);
    }

//    /**
//     * @return LikableEntity[] Returns an array of LikableEntity objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LikableEntity
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
