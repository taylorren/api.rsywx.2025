<?php
/**
 * Consolidated Repository Wiki Export Script
 * 
 * This script creates a single consolidated document containing all wiki content
 * for easy sharing or backup purposes.
 */

class ConsolidatedWikiExporter 
{
    private $projectRoot;
    private $content = [];
    
    public function __construct($projectRoot = null) 
    {
        $this->projectRoot = $projectRoot ?: __DIR__;
    }
    
    public function export($format = 'markdown') 
    {
        echo "📚 Generating Consolidated Wiki Export...\n\n";
        
        // Collect all content
        $this->collectContent();
        
        // Generate output based on format
        switch ($format) {
            case 'markdown':
                $this->exportMarkdown();
                break;
            case 'html':
                $this->exportHTML();
                break;
            case 'txt':
                $this->exportText();
                break;
            default:
                $this->exportMarkdown();
                $this->exportHTML();
                break;
        }
        
        echo "\n✅ Consolidated export completed!\n";
    }
    
    private function collectContent() 
    {
        echo "📖 Collecting documentation content...\n";
        
        // Project overview
        $this->content['overview'] = $this->getFileContent('README.md');
        
        // API documentation
        $this->content['api_docs'] = $this->getFileContent('API_DOCUMENTATION.md');
        
        // Project structure
        $this->content['structure'] = $this->generateProjectStructure();
        
        // Configuration examples
        $this->content['env_config'] = $this->getFileContent('.env.example');
        $this->content['apache_config'] = $this->getFileContent('apache-vhost.conf');
        
        // Database schema info
        $this->content['database'] = $this->getDatabaseInfo();
        
        // Dependencies
        $this->content['dependencies'] = $this->getDependenciesInfo();
        
        // Testing info
        $this->content['testing'] = $this->getTestingInfo();
        
        echo "  ✓ Content collection completed\n";
    }
    
    private function getFileContent($filename) 
    {
        $path = $this->projectRoot . '/' . $filename;
        return file_exists($path) ? file_get_contents($path) : "File not found: {$filename}";
    }
    
    private function generateProjectStructure() 
    {
        $structure = "# Project Structure\n\n";
        $structure .= "```\n";
        $structure .= $this->getDirectoryTree($this->projectRoot);
        $structure .= "```\n\n";
        
        return $structure;
    }
    
    private function getDirectoryTree($dir, $prefix = '', $maxDepth = 3, $currentDepth = 0) 
    {
        if ($currentDepth >= $maxDepth) return '';
        
        $tree = '';
        $items = array_diff(scandir($dir), ['.', '..']);
        $skipDirs = ['vendor', 'node_modules', '.git', 'wiki-export', '.vscode'];
        
        $dirs = [];
        $files = [];
        
        foreach ($items as $item) {
            if ($item[0] === '.' && !in_array($item, ['.env.example', '.gitignore', '.htaccess'])) continue;
            if (in_array($item, $skipDirs)) continue;
            
            if (is_dir($dir . '/' . $item)) {
                $dirs[] = $item;
            } else {
                $files[] = $item;
            }
        }
        
        sort($dirs);
        sort($files);
        
        foreach ($dirs as $subdir) {
            $tree .= $prefix . "├── " . $subdir . "/\n";
            $tree .= $this->getDirectoryTree(
                $dir . '/' . $subdir, 
                $prefix . "│   ", 
                $maxDepth, 
                $currentDepth + 1
            );
        }
        
        foreach ($files as $file) {
            $tree .= $prefix . "├── " . $file . "\n";
        }
        
        return $tree;
    }
    
    private function getDatabaseInfo() 
    {
        $content = "# Database Schema Information\n\n";
        
        $schemaFile = $this->projectRoot . '/schema.rsywx.sql';
        if (file_exists($schemaFile)) {
            $schema = file_get_contents($schemaFile);
            
            // Extract table names
            preg_match_all('/CREATE TABLE `([^`]+)`/', $schema, $matches);
            $tables = $matches[1] ?? [];
            
            $content .= "## Database Tables\n\n";
            foreach ($tables as $table) {
                $content .= "- `{$table}`\n";
            }
            
            $content .= "\n## Schema File Size\n";
            $content .= "- Size: " . number_format(filesize($schemaFile)) . " bytes\n";
            $content .= "- Lines: " . count(file($schemaFile)) . "\n\n";
        }
        
        return $content;
    }
    
    private function getDependenciesInfo() 
    {
        $content = "# Project Dependencies\n\n";
        
        $composerFile = $this->projectRoot . '/composer.json';
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            
            if (isset($composer['require'])) {
                $content .= "## Production Dependencies\n\n";
                foreach ($composer['require'] as $package => $version) {
                    $content .= "- `{$package}`: {$version}\n";
                }
                $content .= "\n";
            }
            
            if (isset($composer['require-dev'])) {
                $content .= "## Development Dependencies\n\n";
                foreach ($composer['require-dev'] as $package => $version) {
                    $content .= "- `{$package}`: {$version}\n";
                }
                $content .= "\n";
            }
        }
        
        return $content;
    }
    
    private function getTestingInfo() 
    {
        $content = "# Testing Information\n\n";
        
        // PHPUnit configuration
        $phpunitFile = $this->projectRoot . '/phpunit.xml';
        if (file_exists($phpunitFile)) {
            $content .= "## PHPUnit Configuration\n\n";
            $content .= "Configuration file: `phpunit.xml`\n\n";
        }
        
        // Test summary
        $testSummaryFile = $this->projectRoot . '/tests/TEST_SUMMARY.md';
        if (file_exists($testSummaryFile)) {
            $content .= "## Test Summary\n\n";
            $content .= file_get_contents($testSummaryFile);
            $content .= "\n";
        }
        
        // Count test files
        $testDir = $this->projectRoot . '/tests';
        if (is_dir($testDir)) {
            $testFiles = glob($testDir . '/**/*Test.php');
            $content .= "## Test Files Count\n\n";
            $content .= "- Total test files: " . count($testFiles) . "\n\n";
        }
        
        return $content;
    }
    
    private function exportMarkdown() 
    {
        echo "📝 Generating Markdown export...\n";
        
        $markdown = "# RSYWX API 2025 - Complete Repository Wiki\n\n";
        $markdown .= "**Generated on:** " . date('Y-m-d H:i:s') . "\n\n";
        $markdown .= "---\n\n";
        
        // Table of contents
        $markdown .= "## Table of Contents\n\n";
        $markdown .= "1. [Project Overview](#project-overview)\n";
        $markdown .= "2. [Project Structure](#project-structure)\n";
        $markdown .= "3. [API Documentation](#api-documentation)\n";
        $markdown .= "4. [Database Information](#database-information)\n";
        $markdown .= "5. [Dependencies](#dependencies)\n";
        $markdown .= "6. [Configuration](#configuration)\n";
        $markdown .= "7. [Testing](#testing)\n\n";
        $markdown .= "---\n\n";
        
        // Content sections
        $markdown .= "# Project Overview\n\n";
        $markdown .= $this->content['overview'] . "\n\n";
        $markdown .= "---\n\n";
        
        $markdown .= $this->content['structure'] . "\n";
        $markdown .= "---\n\n";
        
        $markdown .= "# API Documentation\n\n";
        $markdown .= $this->content['api_docs'] . "\n\n";
        $markdown .= "---\n\n";
        
        $markdown .= $this->content['database'] . "\n";
        $markdown .= "---\n\n";
        
        $markdown .= $this->content['dependencies'] . "\n";
        $markdown .= "---\n\n";
        
        $markdown .= "# Configuration Files\n\n";
        $markdown .= "## Environment Configuration\n\n";
        $markdown .= "```bash\n" . $this->content['env_config'] . "\n```\n\n";
        $markdown .= "## Apache Virtual Host\n\n";
        $markdown .= "```apache\n" . $this->content['apache_config'] . "\n```\n\n";
        $markdown .= "---\n\n";
        
        $markdown .= $this->content['testing'] . "\n";
        
        file_put_contents($this->projectRoot . '/COMPLETE_WIKI.md', $markdown);
        echo "  ✓ Markdown export saved as COMPLETE_WIKI.md\n";
    }
    
    private function exportHTML() 
    {
        echo "🌐 Generating HTML export...\n";
        
        // Convert markdown to basic HTML
        $markdown = file_get_contents($this->projectRoot . '/COMPLETE_WIKI.md');
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSYWX API 2025 - Complete Wiki</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
        h1, h2, h3 { color: #2c3e50; }
        h1 { border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; }
        code { 
            background: #f8f9fa; 
            padding: 2px 4px; 
            border-radius: 3px; 
            font-family: "Monaco", monospace;
        }
        pre { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto; 
            border-left: 4px solid #3498db;
        }
        .toc {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .toc ul { margin: 0; }
        .export-info {
            background: #e8f6f3;
            border: 1px solid #16a085;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>';
        
        // Basic markdown to HTML conversion
        $html .= '<div class="export-info"><strong>📚 Complete Repository Wiki Export</strong><br>';
        $html .= 'Generated on: ' . date('Y-m-d H:i:s') . '</div>';
        
        // Convert headers
        $markdown = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $markdown);
        $markdown = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $markdown);
        
        // Convert code blocks
        $markdown = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre><code>$2</code></pre>', $markdown);
        $markdown = preg_replace('/`([^`]+)`/', '<code>$1</code>', $markdown);
        
        // Convert paragraphs
        $markdown = preg_replace('/\n\n/', '</p><p>', $markdown);
        $markdown = '<p>' . $markdown . '</p>';
        
        // Convert lists
        $markdown = preg_replace('/^- (.+)$/m', '<li>$1</li>', $markdown);
        $markdown = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $markdown);
        
        $html .= $markdown;
        $html .= '</body></html>';
        
        file_put_contents($this->projectRoot . '/COMPLETE_WIKI.html', $html);
        echo "  ✓ HTML export saved as COMPLETE_WIKI.html\n";
    }
    
    private function exportText() 
    {
        echo "📄 Generating plain text export...\n";
        
        $markdown = file_get_contents($this->projectRoot . '/COMPLETE_WIKI.md');
        
        // Strip markdown formatting
        $text = preg_replace('/^#+\s/', '', $markdown);
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        $text = preg_replace('/```[\w]*\n(.*?)\n```/s', '$1', $text);
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);
        
        file_put_contents($this->projectRoot . '/COMPLETE_WIKI.txt', $text);
        echo "  ✓ Plain text export saved as COMPLETE_WIKI.txt\n";
    }
}

// Command line interface
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $format = $argv[1] ?? 'all';
    $exporter = new ConsolidatedWikiExporter();
    $exporter->export($format);
}
?>