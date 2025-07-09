<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\BookTaglist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookTaglist>
 *
 * @method BookTaglist|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookTaglist|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookTaglist[]    findAll()
 * @method BookTaglist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookTaglistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookTaglist::class);
    }

    public function save(BookTaglist $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BookTaglist $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * 获取书籍的所有标签
     *
     * @param int $bookId 书籍ID
     * @return array 标签数组
     */
    public function getTagsByBookId(int $bookId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT tag FROM book_taglist WHERE bid = :bookId";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('bookId', $bookId);
        $result = $stmt->executeQuery();
        $tags = [];
        
        foreach ($result->fetchAllAssociative() as $row) {
            $tags[] = $row['tag'];
        }
        
        return $tags;
    }
}