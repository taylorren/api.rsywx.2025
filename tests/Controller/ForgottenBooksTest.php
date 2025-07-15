<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ForgottenBooksTest extends WebTestCase
{
    public function testGetForgottenBook(): void
    {
        $client = static::createClient();
        $client->request('GET', '/books/forgotten');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n单本最久未访问书籍响应: " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
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
        $this->assertArrayHasKey('city', $book); // 访问区域
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
    
    public function testGetMultipleForgottenBooks(): void
    {
        $client = static::createClient();
        $count = 3; // 请求3本最久未访问的书籍
        
        $client->request('GET', '/books/forgotten/' . $count);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 输出调试信息
        echo "\n多本最久未访问书籍响应 (请求{$count}本): " . json_encode($responseContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        
        // 验证返回的是数组
        $this->assertIsArray($responseContent);
        
        // 验证返回的书籍数量不超过请求的数量
        // 注意：如果数据库中的访问记录少于请求数量，返回的书籍可能少于请求数量
        $this->assertLessThanOrEqual($count, count($responseContent));
        
        // 验证每本书都包含所需的字段
        foreach ($responseContent as $book) {
            $this->assertArrayHasKey('id', $book);
            $this->assertArrayHasKey('bookid', $book);
            $this->assertArrayHasKey('title', $book);
            $this->assertArrayHasKey('author', $book);
            $this->assertArrayHasKey('city', $book); // 访问区域
            $this->assertArrayHasKey('vc', $book); // 访问数量
            $this->assertArrayHasKey('lvt', $book); // 最新一次访问
            $this->assertArrayHasKey('img', $book);
            
            // 验证bookid是5位数字
            $this->assertMatchesRegularExpression('/^\d{5}$/', $book['bookid']);
            
            // 验证img URL格式正确
            $this->assertStringStartsWith('https://api.rsywx.com/covers/', $book['img']);
            $this->assertStringEndsWith('.jpg', $book['img']);
        }
    }
    
    public function testGetTooManyForgottenBooks(): void
    {
        $client = static::createClient();
        $count = 20; // 请求超过限制的书籍数量
        $maxAllowed = 10; // 最大允许的书籍数量
        
        $client->request('GET', '/books/forgotten/' . $count);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertResponseIsSuccessful();
        
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        
        // 验证返回的书籍数量不超过最大允许数量
        $this->assertLessThanOrEqual($maxAllowed, count($responseContent));
    }
    
    public function testGetForgottenBooksWithNegativeCount(): void
    {
        $client = static::createClient();
        $count = -5; // 负数count参数
        
        $client->request('GET', '/books/forgotten/' . $count);
        
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
    
    public function testGetForgottenBooksWithZeroCount(): void
    {
        $client = static::createClient();
        $count = 0; // 零count参数
        
        $client->request('GET', '/books/forgotten/' . $count);
        
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