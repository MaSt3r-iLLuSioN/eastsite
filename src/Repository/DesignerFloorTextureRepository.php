<?php

namespace App\Repository;

use App\Entity\DesignerFloorTexture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DesignerFloorTexture|null find($id, $lockMode = null, $lockVersion = null)
 * @method DesignerFloorTexture|null findOneBy(array $criteria, array $orderBy = null)
 * @method DesignerFloorTexture[]    findAll()
 * @method DesignerFloorTexture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesignerFloorTextureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DesignerFloorTexture::class);
    }

//    /**
//     * @return DesignerFloorTexture[] Returns an array of DesignerFloorTexture objects
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
    public function findOneBySomeField($value): ?DesignerFloorTexture
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
