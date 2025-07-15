<?php

namespace App\Repository\Rsywx;

use App\Entity\Rsywx\BookVisit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Helper\BookHelper;use Doctrine\ORM\Query\ResultSetMapping;

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
    
    /**
     * 获取最近访问的书籍ID列表和访问区域
     *
     * @param int $count 要获取的书籍数量
     * @return array 最近访问的书籍ID和区域信息数组
     */
    public function findRecentVisitedBookIds(int $count = 1): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // 修改查询：返回bookid和region信息
        $sql = "SELECT bookid, region 
                FROM book_visit 
                ORDER BY visitwhen DESC 
                LIMIT :count";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('count', $count, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();
        
        return $result->fetchAllAssociative();
    }
    
    /**
     * 获取最久未访问的书籍ID列表和访问区域
     *
     * @param int $count 要获取的书籍数量
     * @return array 最久未访问的书籍ID和区域信息数组
     */
    public function findForgottenBookIds(int $count = 1): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // 使用与原生SQL相似的查询逻辑
        // 按照最后访问时间升序排序，获取最久未访问的书籍
       $sql = "SELECT b.title, b.bookid, count(v.vid) vc, max(v.visitwhen) lvt FROM book_book b, book_visit v
        where b.id=v.bookid and ".BookHelper::$filter." 
        group by b.id
        order by lvt
        limit 0, :count";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('count', $count, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();
        
        return $result->fetchAllAssociative();
    }
}