<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\BookPlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookPlace>
 *
 * @method BookPlace|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookPlace|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookPlace[]    findAll()
 * @method BookPlace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookPlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookPlace::class);
    }

    public function save(BookPlace $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BookPlace $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}