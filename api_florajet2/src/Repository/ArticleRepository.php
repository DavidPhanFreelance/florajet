<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findByDateFilter(\DateTime $date, string $filter): ?array
    {
        $qb = $this->createQueryBuilder('a');

        switch ($filter) {
            case 'before':
                $qb->andWhere('a.date < :date');
                break;
            case 'after':
                $qb->andWhere('a.date > :date');
                break;
            case 'equal':
                $qb->andWhere('a.date = :date');
                break;
            default:
                throw new \InvalidArgumentException(
                    'Invalid filter parameter, /date/{YYYY-MM-DD/{type}, type must be before | after | equal');
        }

        return $qb
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function findByName($name)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    public function findByAuthor($name)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.author LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    public function findBySource($name)
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.source', 's')
            ->andWhere('s.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }
}