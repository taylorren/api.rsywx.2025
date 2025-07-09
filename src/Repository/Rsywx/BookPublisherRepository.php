<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\BookPublisher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookPublisher>
 *
 * @method BookPublisher|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookPublisher|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookPublisher[]    findAll()
 * @method BookPublisher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookPublisherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookPublisher::class);
    }

    public function save(BookPublisher $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BookPublisher $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}