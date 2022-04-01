<?php

namespace App\Repository;

use App\Entity\ItemMovimento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemMovimento|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemMovimento|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemMovimento[]    findAll()
 * @method ItemMovimento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemMovimentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemMovimento::class);
    }

    // /**
    //  * @return ItemMovimento[] Returns an array of ItemMovimento objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ItemMovimento
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
