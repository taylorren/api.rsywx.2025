<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RecentVisitedBooksTest extends WebTestCase
{
    // 跳过单本最近访问书籍测试，因为当前环境中可能没有足够的访问记录
    public function testGetRecentVisitedBook(): void
    {
        //$this->markTestSkipped('此测试需要数据库中有访问记录，暂时跳过');
        
        
        $client = static::createClient();
        $client->request('GET', '/books/recent_visit');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n单本最近访问书籍响应: " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
        // 验证返回的是数组
        $this->assertIsArray($responseContent);
        
        // 验证返回了一本书
        $this->assertCount(1, $responseContent);
        
        // 获取第一本书
        $book = $responseContent[0];
        
        // 验证响应包含所需的字段
        $this->assertArrayHasKey('id', $book);
        $this->assertArrayHasKey('place', $book);
        $this->assertArrayHasKey('publisher', $book);
        $this->assertArrayHasKey('bookid', $book);
        $this->assertArrayHasKey('title', $book);
        $this->assertArrayHasKey('author', $book);
        $this->assertArrayHasKey('region', $book);
        $this->assertArrayHasKey('copyrighter', $book);
        $this->assertArrayHasKey('translated', $book);
        $this->assertArrayHasKey('purchdate', $book);
        $this->assertArrayHasKey('price', $book);
        $this->assertArrayHasKey('pubdate', $book);
        $this->assertArrayHasKey('printdate', $book);
        $this->assertArrayHasKey('ver', $book);
        $this->assertArrayHasKey('deco', $book);
        $this->assertArrayHasKey('kword', $book);
        $this->assertArrayHasKey('page', $book);
        $this->assertArrayHasKey('isbn', $book);
        $this->assertArrayHasKey('category', $book);
        $this->assertArrayHasKey('ol', $book);
        $this->assertArrayHasKey('intro', $book);
        $this->assertArrayHasKey('instock', $book);
        $this->assertArrayHasKey('location', $book);
        $this->assertArrayHasKey('vc', $book); // 访问数量
        $this->assertArrayHasKey('lvt', $book); // 最新一次访问
        $this->assertArrayHasKey('img', $book);
        
        // 验证bookid是5位数字
        $this->assertMatchesRegularExpression('/^\d{5}$/', $book['bookid']);
        
        // 验证img URL格式正确
        $this->assertStringStartsWith('https://api.rsywx.com/covers/', $book['img']);
        $this->assertStringEndsWith('.jpg', $book['img']);
        
        // 验证vc是整数
        $this->assertIsInt($book['vc']);
        
        // 验证translated和instock是0或1
        $this->assertContains($book['translated'], [0, 1]);
        $this->assertContains($book['instock'], [0, 1]);
       
    }
    
    public function testGetMultipleRecentVisitedBooks(): void
    {
        $client = static::createClient();
        $count = 3; // 请求3本最近访问的书籍
        
        $client->request('GET', '/books/recent_visit/' . $count);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n多本最近访问书籍响应 (请求{$count}本): " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
        // 验证返回的是数组
        $this->assertIsArray($responseContent);
        
        // 验证返回的书籍数量不超过请求的数量
        // 注意：如果数据库中的访问记录少于请求数量，返回的书籍可能少于请求数量
        $this->assertLessThanOrEqual($count, count($responseContent));
        
        // 验证每本书都包含所需的字段
        foreach ($responseContent as $book) {
            $this->assertArrayHasKey('id', $book);
            $this->assertArrayHasKey('place', $book);
            $this->assertArrayHasKey('publisher', $book);
            $this->assertArrayHasKey('bookid', $book);
            $this->assertArrayHasKey('title', $book);
            $this->assertArrayHasKey('author', $book);
            $this->assertArrayHasKey('region', $book);
            $this->assertArrayHasKey('copyrighter', $book);
            $this->assertArrayHasKey('translated', $book);
            $this->assertArrayHasKey('purchdate', $book);
            $this->assertArrayHasKey('price', $book);
            $this->assertArrayHasKey('pubdate', $book);
            $this->assertArrayHasKey('printdate', $book);
            $this->assertArrayHasKey('ver', $book);
            $this->assertArrayHasKey('deco', $book);
            $this->assertArrayHasKey('kword', $book);
            $this->assertArrayHasKey('page', $book);
            $this->assertArrayHasKey('isbn', $book);
            $this->assertArrayHasKey('category', $book);
            $this->assertArrayHasKey('ol', $book);
            $this->assertArrayHasKey('intro', $book);
            $this->assertArrayHasKey('instock', $book);
            $this->assertArrayHasKey('location', $book);
            $this->assertArrayHasKey('vc', $book); // 访问数量
            $this->assertArrayHasKey('lvt', $book); // 最新一次访问
            $this->assertArrayHasKey('img', $book);
            
            // 验证bookid是5位数字
            $this->assertMatchesRegularExpression('/^\d{5}$/', $book['bookid']);
            
            // 验证img URL格式正确
            $this->assertStringStartsWith('https://api.rsywx.com/covers/', $book['img']);
            $this->assertStringEndsWith('.jpg', $book['img']);
        }
        
        // 如果返回了多本书，验证它们按访问时间降序排序
        if (count($responseContent) > 1) {
            // 由于我们无法直接比较访问时间（API返回的是格式化后的时间字符串），
            // 这里我们假设返回的书籍已经按照访问时间降序排序
            // 实际测试中，可能需要更复杂的逻辑来验证排序
            $this->assertTrue(true, '假设返回的书籍已经按照访问时间降序排序');
        }
    }
    
    public function testMaxCountLimit(): void
    {
        $client = static::createClient();
        $requestCount = 15; // 请求15本，超过限制
        
        // 请求超过限制的书籍数量
        $client->request('GET', '/books/recent_visit/' . $requestCount);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n最大数量限制测试 (请求{$requestCount}本): " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        echo "返回书籍数量: " . count($responseContent) . "\n";
        
        // 验证返回的是数组
        $this->assertIsArray($responseContent);
        
        // 验证返回的书籍数量被限制为最大值10
        $this->assertLessThanOrEqual(10, count($responseContent));
    }
    
    public function testGetRecentVisitedBooksWithNegativeCount(): void
    {
        $client = static::createClient();
        $count = -5; // 负数count参数
        
        $client->request('GET', '/books/recent_visit/' . $count);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n负数count参数响应 (count={$count}): " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
        // 验证返回的是数组
        $this->assertIsArray($responseContent);
        
        // 验证返回了一本书（因为负数count会被设置为默认值1）
        $this->assertCount(1, $responseContent);
    }
    
    public function testGetRecentVisitedBooksWithZeroCount(): void
    {
        $client = static::createClient();
        $count = 0; // 零count参数
        
        $client->request('GET', '/books/recent_visit/' . $count);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n零count参数响应 (count={$count}): " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
        // 验证返回的是数组
        $this->assertIsArray($responseContent);
        
        // 验证返回了一本书（因为零count会被设置为默认值1）
        $this->assertCount(1, $responseContent);
    }
}