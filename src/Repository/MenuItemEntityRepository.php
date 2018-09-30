<?php

namespace App\Repository;

use App\Entity\MenuItemEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MenuItemEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuItemEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuItemEntity[]    findAll()
 * @method MenuItemEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuItemEntityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MenuItemEntity::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('m')
            ->where('m.something = :value')->setParameter('value', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
