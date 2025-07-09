<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\BookHeadline;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookHeadline>
 *
 * @method BookHeadline|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookHeadline|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookHeadline[]    findAll()
 * @method BookHeadline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookHeadlineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookHeadline::class);
    }

    public function save(BookHeadline $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BookHeadline $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * 获取书籍的评论标题
     *
     * @param int $bookId 书籍ID
     * @return array|null 评论标题信息
     */
    public function getHeadlineByBookId(int $bookId): ?array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT hid, reviewtitle FROM book_headline WHERE bid = :bookId LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('bookId', $bookId);
        $result = $stmt->executeQuery();
        $data = $result->fetchAssociative();
        
        if (!$data) {
            return null;
        }
        
        return [
            'hid' => (int)$data['hid'],
            'reviewtitle' => $data['reviewtitle']
        ];
    }
}