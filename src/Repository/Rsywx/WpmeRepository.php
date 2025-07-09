<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\Wpme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wpme>
 *
 * @method Wpme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wpme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wpme[]    findAll()
 * @method Wpme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WpmeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wpme::class);
    }

    public function save(Wpme $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Wpme $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}