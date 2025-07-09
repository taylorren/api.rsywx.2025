<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\Wotd;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wotd>
 *
 * @method Wotd|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wotd|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wotd[]    findAll()
 * @method Wotd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WotdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wotd::class);
    }

    public function save(Wotd $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Wotd $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}