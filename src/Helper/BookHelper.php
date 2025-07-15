<?php

namespace App\Helper;

/**
 * 书籍相关的辅助函数类
 */
class BookHelper
{
    /**
     * 验证并规范化书籍数量参数
     * 
     * @param int $count 请求的书籍数量
     * @param int $defaultValue 默认值，当count小于等于0时使用
     * @param int $maxValue 最大值，限制count不超过此值
     * @return int 规范化后的书籍数量
     */
    public static function normalizeBookCount(int $count, int $defaultValue = 1, int $maxValue = 10): int
    {
        // 处理负数和零的情况
        if ($count <= 0) {
            return $defaultValue;
        }
        
        // 限制最大值
        return min($count, $maxValue);
    }
    
    /**
     * 书籍过滤条件
     */
    public static $filter = 'b.location <>"na" and b.location <> "--"';
}