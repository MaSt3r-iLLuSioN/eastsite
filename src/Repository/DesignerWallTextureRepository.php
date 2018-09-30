<?php

namespace App\Repository;

use App\Entity\DesignerWallTexture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DesignerWallTexture|null find($id, $lockMode = null, $lockVersion = null)
 * @method DesignerWallTexture|null findOneBy(array $criteria, array $orderBy = null)
 * @method DesignerWallTexture[]    findAll()
 * @method DesignerWallTexture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesignerWallTextureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DesignerWallTexture::class);
    }

//    /**
//     * @return DesignerWallTexture[] Returns an array of DesignerWallTexture objects
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
    public function findOneBySomeField($value): ?DesignerWallTexture
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
