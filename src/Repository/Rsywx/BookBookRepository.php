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
    
    /**
     * 获取最新收藏的一本书
     *
     * @return BookBook|null
     */
    public function findLatestBook(): ?BookBook
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.purchdate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * 获取一本随机书籍
     *
     * @return BookBook|null
     */
    public function findRandomBook(): ?BookBook
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // 获取书籍总数
        $countSql = "SELECT COUNT(id) as total FROM book_book";
        $countStmt = $conn->prepare($countSql);
        $countResult = $countStmt->executeQuery();
        $count = $countResult->fetchAssociative()['total'];
        
        if ($count <= 0) {
            return null;
        }
        
        // 生成随机偏移量
        $offset = rand(0, $count - 1);
        
        // 使用DQL查询随机书籍
        return $this->createQueryBuilder('b')
            ->setMaxResults(1)
            ->setFirstResult($offset)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    /**
     * 获取多本随机书籍
     *
     * @param int $count 要获取的随机书籍数量
     * @return array 随机书籍数组
     */
    public function findRandomBooks(int $count = 1): array
    {
        if ($count <= 0) {
            return [];
        }
        
        $conn = $this->getEntityManager()->getConnection();
        
        // 获取书籍总数
        $countSql = "SELECT COUNT(id) as total FROM book_book";
        $countStmt = $conn->prepare($countSql);
        $countResult = $countStmt->executeQuery();
        $totalBooks = $countResult->fetchAssociative()['total'];
        
        if ($totalBooks <= 0) {
            return [];
        }
        
        // 如果请求的数量大于总书籍数，则限制为总书籍数
        $count = min($count, $totalBooks);
        
        // 方法1：使用原生SQL查询随机书籍
        $sql = "SELECT b.id FROM book_book b ORDER BY RAND() LIMIT :count";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('count', $count, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();
        $randomBookIds = array_column($result->fetchAllAssociative(), 'id');
        
        if (empty($randomBookIds)) {
            return [];
        }
        
        // 使用ID查询完整的实体对象
        return $this->createQueryBuilder('b')
            ->where('b.id IN (:ids)')
            ->setParameter('ids', $randomBookIds)
            ->getQuery()
            ->getResult();
    }
}