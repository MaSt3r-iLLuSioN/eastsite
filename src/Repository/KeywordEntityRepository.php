<?php

namespace App\Repository;

use App\Entity\KeywordEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method KeywordEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method KeywordEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method KeywordEntity[]    findAll()
 * @method KeywordEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KeywordEntityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, KeywordEntity::class);
    }

//    /**
//     * @return KeywordEntity[] Returns an array of KeywordEntity objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?KeywordEntity
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
