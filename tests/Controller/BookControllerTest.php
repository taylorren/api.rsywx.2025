<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    private function printTestInfo(string $message): void
    {
        fwrite(STDERR, "\n[测试信息] $message\n");
    }
    public function testGetSummary(): void
    {
        $this->printTestInfo('开始测试 /books/summary 接口...');
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
        $this->printTestInfo('开始测试 /books/detail/00001 接口（无评论的书籍）...');
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
          $this->printTestInfo('开始测试不存在的书籍ID 12345...');
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
      
    public function testGetBookDetailWithReviews(): void
    {
        $this->printTestInfo('开始测试 /books/detail/00666 接口（有评论的书籍）...');
        
        // 创建一个客户端来请求应用
        $client = static::createClient();
        
        // 使用一个有评论的书籍ID发送GET请求
        $client->request('GET', '/books/detail/00666');
        
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
        $this->printTestInfo('基本书籍信息字段验证通过');
        
        // 断言新增字段存在
        $this->assertArrayHasKey('total_visits', $responseData);
        $this->assertArrayHasKey('last_visit', $responseData);
        $this->assertArrayHasKey('tags', $responseData);
        $this->assertArrayHasKey('headline', $responseData);
        $this->assertArrayHasKey('reviews', $responseData);
        $this->printTestInfo('扩展信息字段验证通过');
        
        // 断言bookid字段值与请求的bookid匹配
        $this->assertEquals('00666', $responseData['bookid']);
        
        // 断言headline和reviews不为空
        $this->assertNotEmpty($responseData['headline'], '书籍应该有评论标题');
        $this->assertNotEmpty($responseData['reviews'], '书籍应该有评论内容');
        $this->printTestInfo('评论数据验证通过');
        
        // 验证headline是字符串
        $this->assertIsString($responseData['headline'], 'headline应该是字符串');
        
        // 验证reviews是数组
        $this->assertIsArray($responseData['reviews'], 'reviews应该是数组');
        
        // 如果reviews不为空，验证其结构
        if (!empty($responseData['reviews'])) {
            // 获取第一个评论进行结构验证
            $firstReview = $responseData['reviews'][0];
            $this->printTestInfo('验证评论数据结构');
            
            // 根据实际的评论数据结构进行断言
            $this->assertIsArray($firstReview, '评论应该是数组');
            $this->assertArrayHasKey('title', $firstReview, '评论应包含title字段');
            $this->assertArrayHasKey('date', $firstReview, '评论应包含date字段');
            $this->assertArrayHasKey('uri', $firstReview, '评论应包含uri字段');
            $this->assertArrayHasKey('feature', $firstReview, '评论应包含feature字段');
        }
        $this->printTestInfo('评论数据结构验证通过');
    }
}