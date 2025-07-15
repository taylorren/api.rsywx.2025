<?php

namespace App\Controller;

use App\Entity\Rsywx\BookBook;
use App\Repository\Rsywx\BookBookRepository;
use App\Repository\Rsywx\BookVisitRepository;
use App\Repository\Rsywx\BookHeadlineRepository;
use App\Repository\Rsywx\BookReviewRepository;
use App\Repository\Rsywx\BookTaglistRepository;
use App\Helper\BookHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BookController extends AbstractController
{
    private $entityManager;
    private $bookRepository;
    private $visitRepository;
    private $headlineRepository;
    private $reviewRepository;
    private $taglistRepository;
    private $cache;

    public function __construct(
        EntityManagerInterface $entityManager, 
        BookBookRepository $bookRepository,
        BookVisitRepository $visitRepository,
        BookHeadlineRepository $headlineRepository,
        BookReviewRepository $reviewRepository,
        BookTaglistRepository $taglistRepository,
        CacheInterface $cache
    ) {   
        $this->entityManager = $entityManager;
        $this->bookRepository = $bookRepository;
        $this->visitRepository = $visitRepository;
        $this->headlineRepository = $headlineRepository;
        $this->reviewRepository = $reviewRepository;
        $this->taglistRepository = $taglistRepository;
        $this->cache = $cache;
    }

    #[Route('/books/summary', name: 'app_books_summary', methods: ['GET'])]
    public function getSummary(): JsonResponse
    {
        $conn = $this->entityManager->getConnection();
        
        // 使用原生SQL查询获取统计数据
        $sql = "SELECT 
                COUNT(*) as bc, 
                SUM(kword) as wc, 
                SUM(page) as pc 
                FROM book_book";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $data = $result->fetchAssociative();
        
        // 确保返回的数据是整数
        $response = [
            'bc' => (int)$data['bc'],
            'wc' => (int)$data['wc'],
            'pc' => (int)$data['pc']
        ];
        
        return new JsonResponse($response);
    }
    
    #[Route('/books/detail/{bookid}', name: 'app_book_detail', methods: ['GET'], requirements: ['bookid' => '\d{5}'])]    
    public function getBookDetail(string $bookid): JsonResponse
    {
        // 使用缓存，缓存有效期为1小时（3600秒）
        return new JsonResponse($this->cache->get('book_detail_'.$bookid, function (ItemInterface $item) use ($bookid) {
            $item->expiresAfter(3600); // 缓存1小时
            
            // 查找指定bookid的书籍
            $book = $this->bookRepository->findOneBy(['bookid' => $bookid]);
            
            // 如果没有找到书籍，抛出404异常
            if (!$book) {
                throw new NotFoundHttpException(sprintf('没有找到ID为%s的书籍', $bookid));
            }
            
            $bookId = $book->getId();
            
            // 使用优化的查询方法获取访问统计信息
            $visitStats = $this->visitRepository->getVisitStatistics($bookId);
            $totalVisits = $visitStats['total_visits'];
            $lastVisit = $visitStats['last_visit'];
            
            // 使用优化的查询方法获取标签
            $tags = $this->taglistRepository->getTagsByBookId($bookId);
            
            // 使用优化的查询方法获取评论标题
            $headlineData = $this->headlineRepository->getHeadlineByBookId($bookId);
            $headlineTitle = null;
            $reviews = [];
            
            // 如果有评论标题，获取相关评论
            if ($headlineData) {
                $headlineTitle = $headlineData['reviewtitle'];
                $headlineId = $headlineData['hid'];
                
                // 使用优化的查询方法获取评论
                $reviews = $this->reviewRepository->getReviewsByHeadlineId($headlineId);
            }
            
            // 构建响应数据 - 返回所有字段
            return [
                'id' => $book->getId(),
                'bookid' => $book->getBookid(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'region' => $book->getRegion(),
                'copyrighter' => $book->getCopyrighter(),
                'translated' => $book->isTranslated(),
                'purchdate' => $book->getPurchdate() ? $book->getPurchdate()->format('Y-m-d') : null,
                'price' => $book->getPrice(),
                'pubdate' => $book->getPubdate() ? $book->getPubdate()->format('Y-m-d') : null,
                'printdate' => $book->getPrintdate() ? $book->getPrintdate()->format('Y-m-d') : null,
                'ver' => $book->getVer(),
                'deco' => $book->getDeco(),
                'kword' => $book->getKword(),
                'page' => $book->getPage(),
                'isbn' => $book->getIsbn(),
                'category' => $book->getCategory(),
                'ol' => $book->getOl(),
                'intro' => $book->getIntro(),
                'instock' => $book->isInstock(),
                'location' => $book->getLocation(),
                'publisher' => $book->getPublisher() ? $book->getPublisher()->getName() : null,
                'place' => $book->getPlace() ? $book->getPlace()->getName() : null,
                'img' => 'https://api.rsywx.com/covers/' . $book->getBookid() . '.jpg',
                'total_visits' => $totalVisits,
                'last_visit' => $lastVisit,
                'tags' => $tags,
                'headline' => $headlineTitle,
                'reviews' => $reviews
            ];
        }));
    
    }
    
    #[Route('/books/latest', name: 'app_books_latest', methods: ['GET'])]
    public function getLatestBook(): JsonResponse
    {
        // 使用缓存，缓存有效期为1小时（3600秒）
        return new JsonResponse($this->cache->get('book_latest', function (ItemInterface $item) {
            $item->expiresAfter(3600); // 缓存1小时
            
            // 查找最新收藏的书籍
            $book = $this->bookRepository->findLatestBook();
            
            // 如果没有找到书籍，抛出404异常
            if (!$book) {
                throw new NotFoundHttpException('没有找到任何书籍');
            }
            
            // 构建响应数据 - 只返回指定字段
            return [
                'bookid' => $book->getBookid(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'cover' => 'https://api.rsywx.com/covers/' . $book->getBookid() . '.jpg',
                'purchdate' => $book->getPurchdate() ? $book->getPurchdate()->format('Y-m-d') : null
            ];
        }));
    }
    
    #[Route('/books/random/{count}', name: 'app_books_random', methods: ['GET'], requirements: ['count' => '-?\d+'])]
    public function getRandomBook(int $count = 1): JsonResponse
    {
        // 使用BookHelper处理count参数
        $count = BookHelper::normalizeBookCount($count);
        
        // 不使用缓存，每次请求都返回随机书籍
        // 查找随机书籍
        $books = $this->bookRepository->findRandomBooks($count);
        
        // 如果没有找到书籍，返回空数组而不是抛出404异常
        if (empty($books)) {
            return new JsonResponse([]);
        }
        
        // 构建响应数据
        $response = [];
        
        foreach ($books as $book) {
            $bookId = $book->getId();
            
            // 获取访问统计信息
            $visitStats = $this->visitRepository->getVisitStatistics($bookId);
            $totalVisits = $visitStats['total_visits'];
            $lastVisit = $visitStats['last_visit'];
            
            $response[] = [
                'id' => $bookId,
                'place' => $book->getPlace() ? $book->getPlace()->getId() : null,
                'publisher' => $book->getPublisher() ? $book->getPublisher()->getId() : null,
                'bookid' => $book->getBookid(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'region' => $book->getRegion(),
                'city' => $visitRegions[$bookId] ?? null, // 添加访问区域信息
                'copyrighter' => $book->getCopyrighter(),
                'translated' => $book->isTranslated() ? 1 : 0,
                'purchdate' => $book->getPurchdate() ? $book->getPurchdate()->format('Y-m-d') : null,
                'price' => $book->getPrice(),
                'pubdate' => $book->getPubdate() ? $book->getPubdate()->format('Y-m-d') : null,
                'printdate' => $book->getPrintdate() ? $book->getPrintdate()->format('Y-m-d') : null,
                'ver' => $book->getVer(),
                'deco' => $book->getDeco(),
                'kword' => $book->getKword(),
                'page' => $book->getPage(),
                'isbn' => $book->getIsbn(),
                'category' => $book->getCategory(),
                'ol' => $book->getOl(),
                'intro' => $book->getIntro(),
                'instock' => $book->isInstock() ? 1 : 0,
                'location' => $book->getLocation(),
                'vc' => $totalVisits, // 访问数量
                'lvt' => $lastVisit, // 最新一次访问
                'img' => 'https://api.rsywx.com/covers/' . $book->getBookid() . '.jpg',
            ];
        }
        
        // 始终返回数组格式，即使只有一本书
        return new JsonResponse($response);
    }
    
    #[Route('/books/recent_visit/{count}', name: 'app_books_recent_visit', methods: ['GET'], requirements: ['count' => '-?\d+'], defaults: ['count' => 1])]
    public function getRecentVisitedBooks(int $count = 1): JsonResponse
    {
        // 使用BookHelper处理count参数
        $count = BookHelper::normalizeBookCount($count);
        
        // 不使用缓存，每次请求都返回最新数据
        // 获取最近访问的书籍ID和访问区域
        $visitData = $this->visitRepository->findRecentVisitedBookIds($count);
        
        // 提取书籍ID和访问区域信息
        $bookIds = array_column($visitData, 'bookid');
        $visitRegions = array_column($visitData, 'region', 'bookid');
        
        // 根据ID获取书籍实体
        $books = $this->bookRepository->findBy(['id' => $bookIds]);
        
        // 如果没有找到书籍，返回空数组而不是抛出404异常
        if (empty($books)) {
            return new JsonResponse([]);
        }
        
        // 构建响应数据
        $response = [];
        
        foreach ($books as $book) {
            $bookId = $book->getId();
            
            // 获取访问统计信息
            $visitStats = $this->visitRepository->getVisitStatistics($bookId);
            $totalVisits = $visitStats['total_visits'];
            $lastVisit = $visitStats['last_visit'];
            
            $response[] = [
                'id' => $bookId,
                'place' => $book->getPlace() ? $book->getPlace()->getId() : null,
                'publisher' => $book->getPublisher() ? $book->getPublisher()->getId() : null,
                'bookid' => $book->getBookid(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'region' => $book->getRegion(),
                'city' => $visitRegions[$bookId] ?? null, // 添加访问区域信息
                'copyrighter' => $book->getCopyrighter(),
                'translated' => $book->isTranslated() ? 1 : 0,
                'purchdate' => $book->getPurchdate() ? $book->getPurchdate()->format('Y-m-d') : null,
                'price' => $book->getPrice(),
                'pubdate' => $book->getPubdate() ? $book->getPubdate()->format('Y-m-d') : null,
                'printdate' => $book->getPrintdate() ? $book->getPrintdate()->format('Y-m-d') : null,
                'ver' => $book->getVer(),
                'deco' => $book->getDeco(),
                'kword' => $book->getKword(),
                'page' => $book->getPage(),
                'isbn' => $book->getIsbn(),
                'category' => $book->getCategory(),
                'ol' => $book->getOl(),
                'intro' => $book->getIntro(),
                'instock' => $book->isInstock() ? 1 : 0,
                'location' => $book->getLocation(),
                'vc' => $totalVisits, // 访问数量
                'lvt' => $lastVisit, // 最新一次访问
                'img' => 'https://api.rsywx.com/covers/' . $book->getBookid() . '.jpg',
            ];
        }
        
        // 始终返回数组格式，即使只有一本书
        return new JsonResponse($response);
    }
    
    #[Route('/books/forgotten/{count}', name: 'app_books_forgotten', methods: ['GET'], requirements: ['count' => '-?\d+'], defaults: ['count' => 1])]    
public function getForgottenBooks(int $count = 1): JsonResponse
    {
        // 使用BookHelper处理count参数
        $count = BookHelper::normalizeBookCount($count);
        
        // 获取最久未访问的书籍ID和访问区域
        $visitData = $this->visitRepository->findForgottenBookIds($count);
        
        // 提取书籍ID和访问区域信息
        $bookIds = array_column($visitData, 'bookid');
        $visitRegions = array_column($visitData, 'region', 'bookid');
        
        // 根据ID获取书籍实体
        $books = $this->bookRepository->findBy(['id' => $bookIds]);
        
        // 如果没有找到书籍，返回空数组而不是抛出404异常
        if (empty($books)) {
            return new JsonResponse([]);
        }
        
        // 构建响应数据
        $response = [];
        
        foreach ($books as $book) {
            $bookId = $book->getId();
            
            // 获取访问统计信息
            $visitStats = $this->visitRepository->getVisitStatistics($bookId);
            
            $totalVisits = $visitStats['total_visits'];
            $lastVisit = $visitStats['last_visit'];
            
            $response[] = [
                'id' => $bookId,
                'place' => $book->getPlace() ? $book->getPlace()->getId() : null,
                'publisher' => $book->getPublisher() ? $book->getPublisher()->getId() : null,
                'bookid' => $book->getBookid(),
                'title' => $book->getTitle(),
                'author' => $book->getAuthor(),
                'region' => $book->getRegion(),
                'city' => $visitRegions[$bookId] ?? null, // 添加访问区域信息
                'copyrighter' => $book->getCopyrighter(),
                'translated' => $book->isTranslated() ? 1 : 0,
                'purchdate' => $book->getPurchdate() ? $book->getPurchdate()->format('Y-m-d') : null,
                'price' => $book->getPrice(),
                'pubdate' => $book->getPubdate() ? $book->getPubdate()->format('Y-m-d') : null,
                'printdate' => $book->getPrintdate() ? $book->getPrintdate()->format('Y-m-d') : null,
                'ver' => $book->getVer(),
                'deco' => $book->getDeco(),
                'kword' => $book->getKword(),
                'page' => $book->getPage(),
                'isbn' => $book->getIsbn(),
                'category' => $book->getCategory(),
                'ol' => $book->getOl(),
                'intro' => $book->getIntro(),
                'instock' => $book->isInstock() ? 1 : 0,
                'location' => $book->getLocation(),
                'vc' => $totalVisits, // 访问数量
                'lvt' => $lastVisit, // 最新一次访问
                'img' => 'https://api.rsywx.com/covers/' . $book->getBookid() . '.jpg',
            ];
        }
        
        // 始终返回数组格式，即使只有一本书
        return new JsonResponse($response);
    }
}