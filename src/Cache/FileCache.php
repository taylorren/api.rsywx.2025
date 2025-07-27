<?php

namespace App\Cache;

class FileCache
{
    private $cacheDir;
    private $defaultTtl;

    public function __construct($cacheDir = null, $defaultTtl = 86400)
    {
        $this->cacheDir = $cacheDir ?: __DIR__ . '/../../cache';
        $this->defaultTtl = $defaultTtl; // 24 hours default
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key)
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($filename), true);
        
        if (!$data || !isset($data['expires_at']) || !isset($data['value'])) {
            return null;
        }
        
        // Check if cache has expired
        if (time() > $data['expires_at']) {
            $this->delete($key);
            return null;
        }
        
        return $data['value'];
    }

    public function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?: $this->defaultTtl;
        $filename = $this->getCacheFilename($key);
        
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];
        
        return file_put_contents($filename, json_encode($data)) !== false;
    }

    public function delete($key)
    {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }

    public function clear()
    {
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }

    public function has($key)
    {
        return $this->get($key) !== null;
    }

    private function getCacheFilename($key)
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }

    public function getStats()
    {
        $files = glob($this->cacheDir . '/*.cache');
        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'entries' => []
        ];
        
        foreach ($files as $file) {
            $size = filesize($file);
            $stats['total_size'] += $size;
            
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $key = basename($file, '.cache');
                $stats['entries'][$key] = [
                    'size' => $size,
                    'created_at' => $data['created_at'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'expired' => time() > ($data['expires_at'] ?? 0)
                ];
            }
        }
        
        return $stats;
    }
}