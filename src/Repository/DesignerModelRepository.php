<?php

namespace App\Repository;

use App\Entity\DesignerModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DesignerModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method DesignerModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method DesignerModel[]    findAll()
 * @method DesignerModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesignerModelRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DesignerModel::class);
    }

//    /**
//     * @return DesignerModel[] Returns an array of DesignerModel objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DesignerModel
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
