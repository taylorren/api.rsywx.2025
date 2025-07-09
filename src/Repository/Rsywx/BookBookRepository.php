<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\BookBook;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookBook>
 *
 * @method BookBook|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookBook|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookBook[]    findAll()
 * @method BookBook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookBookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookBook::class);
    }

    public function save(BookBook $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BookBook $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}