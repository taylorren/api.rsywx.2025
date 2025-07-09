<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\BookReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookReview>
 *
 * @method BookReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookReview[]    findAll()
 * @method BookReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookReview::class);
    }

    public function save(BookReview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BookReview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * 获取指定评论标题下的所有评论
     *
     * @param int $headlineId 评论标题ID
     * @return array 评论数组
     */
    public function getReviewsByHeadlineId(int $headlineId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT title, datein, uri, feature FROM book_review WHERE hid = :headlineId";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('headlineId', $headlineId);
        $result = $stmt->executeQuery();
        $reviews = [];
        
        foreach ($result->fetchAllAssociative() as $row) {
            $reviews[] = [
                'title' => $row['title'],
                'date' => $row['datein'] ? date('Y-m-d', strtotime($row['datein'])) : null,
                'uri' => $row['uri'],
                'feature' => $row['feature']
            ];
        }
        
        return $reviews;
    }
}