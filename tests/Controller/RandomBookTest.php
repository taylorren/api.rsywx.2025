<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RandomBookTest extends WebTestCase
{
    public function testGetRandomBook(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books/random');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n单本随机书籍响应: " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
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
    
    public function testGetMultipleRandomBooks(): void
    {
        $client = static::createClient();
        $count = 3; // 请求3本随机书籍
        
        $client->request('GET', '/books/random/' . $count);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n多本随机书籍响应 (请求{$count}本): " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
        // 验证返回的是数组
        $this->assertIsArray($responseContent);
        
        // 验证返回了正确数量的书籍
        $this->assertCount($count, $responseContent);
        
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
        
        // 验证返回的书籍不全部相同
        $bookIds = array_column($responseContent, 'bookid');
        $uniqueBookIds = array_unique($bookIds);
        
        // 注意：这个测试在极少数情况下可能会失败，如果随机选择了相同的书
        $this->assertGreaterThan(1, count($uniqueBookIds), '返回的多本随机书籍应该不全部相同');
    }
    
    public function testMultipleRandomBooks(): void
    {
        $client = static::createClient();
        
        // 获取第一本随机书籍
        $client->request('GET', '/books/random');
        $this->assertResponseIsSuccessful();
        $firstResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($firstResponse);
        $this->assertCount(1, $firstResponse);
        $firstBook = $firstResponse[0];
        echo "\n随机性测试 - 第一本书: " . json_encode($firstBook, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 获取第二本随机书籍
        $client->request('GET', '/books/random');
        $this->assertResponseIsSuccessful();
        $secondResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($secondResponse);
        $this->assertCount(1, $secondResponse);
        $secondBook = $secondResponse[0];
        echo "随机性测试 - 第二本书: " . json_encode($secondBook, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 获取第三本随机书籍
        $client->request('GET', '/books/random');
        $this->assertResponseIsSuccessful();
        $thirdResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($thirdResponse);
        $this->assertCount(1, $thirdResponse);
        $thirdBook = $thirdResponse[0];
        echo "随机性测试 - 第三本书: " . json_encode($thirdBook, JSON_UNESCAPED_UNICODE) . "\n";
        
        // 检查是否至少有一本书不同（注意：这个测试在极少数情况下可能会失败，如果随机选择了相同的书）
        $allSame = true;
        if ($firstBook['bookid'] !== $secondBook['bookid'] || 
            $firstBook['bookid'] !== $thirdBook['bookid'] || 
            $secondBook['bookid'] !== $thirdBook['bookid']) {
            $allSame = false;
        }
        
        echo "所有书籍相同: " . ($allSame ? 'true' : 'false') . "\n";
        
        $this->assertFalse($allSame, '多次请求随机书籍API应该返回不同的书籍');
    }
    
    public function testMaxBooksLimit(): void
    {
        $client = static::createClient();
        $requestCount = 15; // 请求15本，超过限制
        
        // 请求超过限制的书籍数量
        $client->request('GET', '/books/random/' . $requestCount);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n最大数量限制测试 (请求{$requestCount}本): " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        echo "返回书籍数量: " . count($responseContent) . "\n";
        
        // 验证返回的是数组
        $this->assertIsArray($responseContent);
        
        // 验证返回的书籍数量被限制为最大值10
        $this->assertCount(10, $responseContent);
    }
    
    public function testGetRandomBookWithNegativeCount(): void
    {
        $client = static::createClient();
        $count = -5; // 负数count参数
        
        $client->request('GET', '/books/random/' . $count);
        
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
    
    public function testGetRandomBookWithZeroCount(): void
    {
        $client = static::createClient();
        $count = 0; // 零count参数
        
        $client->request('GET', '/books/random/' . $count);
        
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