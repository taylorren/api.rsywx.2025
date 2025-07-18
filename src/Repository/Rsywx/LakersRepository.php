<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\Lakers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lakers>
 *
 * @method Lakers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lakers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lakers[]    findAll()
 * @method Lakers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LakersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lakers::class);
    }

    public function save(Lakers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Lakers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}