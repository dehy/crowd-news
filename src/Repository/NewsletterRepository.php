<?php

namespace App\Repository;

use App\Entity\Newsletter;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Newsletter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Newsletter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Newsletter[]    findAll()
 * @method Newsletter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsletterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Newsletter::class);
    }

    public function findSent(): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.sentAt IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findLast(): Newsletter
    {
        return $this->createQueryBuilder('n')
            ->where('n.sentAt IS NOT NULL')
            ->orderBy('n.sentAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findScheduled(): ?Newsletter
    {
        return $this->createQueryBuilder('n')
            ->where('n.sentAt IS NULL')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
