<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    public function testGetSummary(): void
    {
        // 创建一个客户端来请求应用
        $client = static::createClient();
        
        // 发送GET请求到/books/summary端点
        $client->request('GET', '/books/summary');
        
        // 断言HTTP状态码为200
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // 断言响应是JSON格式
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
        
        // 获取响应内容并解码JSON
        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        
        // 断言JSON响应包含三个必要的字段：bc, wc, pc
        $this->assertArrayHasKey('bc', $responseData);
        $this->assertArrayHasKey('wc', $responseData);
        $this->assertArrayHasKey('pc', $responseData);
        
        // 断言这些字段的值是整数
        $this->assertIsInt($responseData['bc']);
        $this->assertIsInt($responseData['wc']);
        $this->assertIsInt($responseData['pc']);
    }
    
    public function testGetBookDetail(): void
    {
        // 创建一个客户端来请求应用
        $client = static::createClient();
        
        // 使用一个有效的5位数字bookid发送GET请求到/books/detail/{bookid}端点
        // 注意：这里使用的bookid应该是数据库中存在的，如果测试失败，请替换为实际存在的bookid
        $client->request('GET', '/books/detail/00001');
        
        // 断言HTTP状态码为200
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        // 断言响应是JSON格式
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
        
        // 获取响应内容并解码JSON
        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        
        // 断言JSON响应包含所有必要的字段
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('bookid', $responseData);
        $this->assertArrayHasKey('title', $responseData);
        $this->assertArrayHasKey('author', $responseData);
        $this->assertArrayHasKey('region', $responseData);
        $this->assertArrayHasKey('copyrighter', $responseData);
        $this->assertArrayHasKey('translated', $responseData);
        $this->assertArrayHasKey('purchdate', $responseData);
        $this->assertArrayHasKey('price', $responseData);
        $this->assertArrayHasKey('pubdate', $responseData);
        $this->assertArrayHasKey('printdate', $responseData);
        $this->assertArrayHasKey('ver', $responseData);
        $this->assertArrayHasKey('deco', $responseData);
        $this->assertArrayHasKey('kword', $responseData);
        $this->assertArrayHasKey('page', $responseData);
        $this->assertArrayHasKey('isbn', $responseData);
        $this->assertArrayHasKey('category', $responseData);
        $this->assertArrayHasKey('ol', $responseData);
        $this->assertArrayHasKey('intro', $responseData);
        $this->assertArrayHasKey('instock', $responseData);
        $this->assertArrayHasKey('location', $responseData);
        $this->assertArrayHasKey('publisher', $responseData);
        $this->assertArrayHasKey('place', $responseData);
        $this->assertArrayHasKey('img', $responseData);
        
        // 断言新增字段存在
        $this->assertArrayHasKey('total_visits', $responseData);
        $this->assertArrayHasKey('last_visit', $responseData);
        $this->assertArrayHasKey('tags', $responseData);
        $this->assertArrayHasKey('headline', $responseData);
        $this->assertArrayHasKey('reviews', $responseData);
        
        // 断言新增字段的类型
        $this->assertIsInt($responseData['total_visits']);
        $this->assertIsArray($responseData['tags']);
        $this->assertIsArray($responseData['reviews']);
        
        // 断言bookid字段值与请求的bookid匹配
        $this->assertEquals('00001', $responseData['bookid']);
        
        // 断言img字段值符合预期格式
        $expectedImgUrl = 'https://api.rsywx.com/covers/00001.jpg';
        $this->assertEquals($expectedImgUrl, $responseData['img']);
    }
    
    public function testGetBookDetailNotFound(): void
      {
          // 创建一个客户端来请求应用
          $client = static::createClient();
          
          // 使用一个不存在的5位数字bookid发送GET请求到/books/detail/{bookid}端点
          $client->request('GET', '/books/detail/12345');
          
          // 断言HTTP状态码为404（资源未找到）
          $this->assertEquals(404, $client->getResponse()->getStatusCode());
          
          // 获取响应内容
          $responseContent = $client->getResponse()->getContent();
          
          // 断言响应内容包含预期的错误信息
          $this->assertStringContainsString('没有找到ID为12345的书籍', $responseContent);
      }
}