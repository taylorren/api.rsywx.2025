<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\BookVisit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @extends ServiceEntityRepository<BookVisit>
 *
 * @method BookVisit|null find($id, $lockMode = null, $lockVersion = null)
 * @method BookVisit|null findOneBy(array $criteria, array $orderBy = null)
 * @method BookVisit[]    findAll()
 * @method BookVisit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookVisit::class);
    }

    public function save(BookVisit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BookVisit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * 获取书籍的访问统计信息
     *
     * @param int $bookId 书籍ID
     * @return array 包含总访问量和最后访问时间的数组
     */
    public function getVisitStatistics(int $bookId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT 
                COUNT(*) as total_visits,
                MAX(visitwhen) as last_visit
                FROM book_visit
                WHERE bookid = :bookId";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('bookId', $bookId);
        $result = $stmt->executeQuery();
        $data = $result->fetchAssociative();
        
        return [
            'total_visits' => (int)$data['total_visits'],
            'last_visit' => $data['last_visit']
        ];
    }
}