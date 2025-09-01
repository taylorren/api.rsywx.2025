<?php
/**
 * Repository Wiki Export Script
 * 
 * This script exports all documentation and wiki content from the repository
 * into organized formats for backup or sharing purposes.
 */

class WikiExporter 
{
    private $projectRoot;
    private $exportDir;
    
    public function __construct($projectRoot = null) 
    {
        $this->projectRoot = $projectRoot ?: __DIR__;
        $this->exportDir = $this->projectRoot . '/wiki-export';
    }
    
    public function export() 
    {
        echo "🚀 Starting Repository Wiki Export...\n\n";
        
        // Create export directory
        if (!is_dir($this->exportDir)) {
            mkdir($this->exportDir, 0755, true);
        }
        
        // Export different documentation types
        $this->exportAPIDocumentation();
        $this->exportProjectDocumentation();
        $this->exportDatabaseSchema();
        $this->exportConfigurationFiles();
        $this->exportTestDocumentation();
        
        // Create index file
        $this->createExportIndex();
        
        echo "\n✅ Wiki export completed successfully!\n";
        echo "📁 Export location: {$this->exportDir}\n";
        echo "📋 Open 'index.html' to browse all exported content\n\n";
    }
    
    private function exportAPIDocumentation() 
    {
        echo "📖 Exporting API Documentation...\n";
        
        $apiDir = $this->exportDir . '/api-documentation';
        if (!is_dir($apiDir)) {
            mkdir($apiDir, 0755, true);
        }
        
        // Copy OpenAPI documentation files
        $apiFiles = [
            'public/api-docs.html' => 'interactive-docs.html',
            'public/api-docs.json' => 'openapi-spec.json',
            'public/api-docs.yaml' => 'openapi-spec.yaml',
            'public/api-docs.css' => 'docs-styles.css',
            'API_DOCUMENTATION.md' => 'api-documentation.md'
        ];
        
        foreach ($apiFiles as $source => $target) {
            $sourcePath = $this->projectRoot . '/' . $source;
            $targetPath = $apiDir . '/' . $target;
            
            if (file_exists($sourcePath)) {
                copy($sourcePath, $targetPath);
                echo "  ✓ Copied {$source} → {$target}\n";
            }
        }
    }
    
    private function exportProjectDocumentation() 
    {
        echo "📚 Exporting Project Documentation...\n";
        
        $docsDir = $this->exportDir . '/project-documentation';
        if (!is_dir($docsDir)) {
            mkdir($docsDir, 0755, true);
        }
        
        // Copy main documentation files
        $docFiles = [
            'README.md' => 'readme.md',
            'composer.json' => 'dependencies.json',
            'phpunit.xml' => 'testing-config.xml'
        ];
        
        foreach ($docFiles as $source => $target) {
            $sourcePath = $this->projectRoot . '/' . $source;
            $targetPath = $docsDir . '/' . $target;
            
            if (file_exists($sourcePath)) {
                copy($sourcePath, $targetPath);
                echo "  ✓ Copied {$source} → {$target}\n";
            }
        }
        
        // Create project structure documentation
        $this->createProjectStructureDoc($docsDir);
    }
    
    private function exportDatabaseSchema() 
    {
        echo "🗄️  Exporting Database Schema...\n";
        
        $dbDir = $this->exportDir . '/database-schema';
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $schemaFile = $this->projectRoot . '/schema.rsywx.sql';
        if (file_exists($schemaFile)) {
            copy($schemaFile, $dbDir . '/complete-schema.sql');
            echo "  ✓ Copied database schema\n";
        }
    }
    
    private function exportConfigurationFiles() 
    {
        echo "⚙️  Exporting Configuration Files...\n";
        
        $configDir = $this->exportDir . '/configuration';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        $configFiles = [
            '.env.example' => 'environment-template.txt',
            'apache-vhost.conf' => 'apache-virtual-host.conf',
            '.gitignore' => 'git-ignore-rules.txt'
        ];
        
        foreach ($configFiles as $source => $target) {
            $sourcePath = $this->projectRoot . '/' . $source;
            $targetPath = $configDir . '/' . $target;
            
            if (file_exists($sourcePath)) {
                copy($sourcePath, $targetPath);
                echo "  ✓ Copied {$source} → {$target}\n";
            }
        }
    }
    
    private function exportTestDocumentation() 
    {
        echo "🧪 Exporting Test Documentation...\n";
        
        $testDir = $this->exportDir . '/testing';
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        
        // Copy test summary if it exists
        $testSummary = $this->projectRoot . '/tests/TEST_SUMMARY.md';
        if (file_exists($testSummary)) {
            copy($testSummary, $testDir . '/test-summary.md');
            echo "  ✓ Copied test summary\n";
        }
        
        // Copy phpunit configuration
        $phpunitConfig = $this->projectRoot . '/phpunit.xml';
        if (file_exists($phpunitConfig)) {
            copy($phpunitConfig, $testDir . '/phpunit-config.xml');
            echo "  ✓ Copied PHPUnit configuration\n";
        }
    }
    
    private function createProjectStructureDoc($docsDir) 
    {
        $structureContent = "# Project Structure\n\n";
        $structureContent .= "This document outlines the complete project structure and organization.\n\n";
        $structureContent .= "## Directory Structure\n\n";
        $structureContent .= "```\n";
        $structureContent .= $this->generateDirectoryTree($this->projectRoot);
        $structureContent .= "```\n\n";
        
        $structureContent .= "## Key Components\n\n";
        $structureContent .= "- **public/**: Web server document root with API entry point\n";
        $structureContent .= "- **src/**: Application source code organized by MVC pattern\n";
        $structureContent .= "- **tests/**: Unit and integration tests\n";
        $structureContent .= "- **vendor/**: Composer dependencies\n";
        $structureContent .= "- **cache/**: Application cache storage\n\n";
        
        file_put_contents($docsDir . '/project-structure.md', $structureContent);
        echo "  ✓ Generated project structure documentation\n";
    }
    
    private function generateDirectoryTree($dir, $prefix = '', $maxDepth = 3, $currentDepth = 0) 
    {
        if ($currentDepth >= $maxDepth) {
            return '';
        }
        
        $tree = '';
        $items = array_diff(scandir($dir), ['.', '..']);
        $dirs = [];
        $files = [];
        
        // Separate directories and files
        foreach ($items as $item) {
            if ($item[0] === '.') continue; // Skip hidden files
            if (in_array($item, ['vendor', 'node_modules', '.git'])) continue; // Skip large dirs
            
            if (is_dir($dir . '/' . $item)) {
                $dirs[] = $item;
            } else {
                $files[] = $item;
            }
        }
        
        // Sort arrays
        sort($dirs);
        sort($files);
        
        // Add directories first
        foreach ($dirs as $subdir) {
            $tree .= $prefix . "├── " . $subdir . "/\n";
            $tree .= $this->generateDirectoryTree(
                $dir . '/' . $subdir, 
                $prefix . "│   ", 
                $maxDepth, 
                $currentDepth + 1
            );
        }
        
        // Add files
        foreach ($files as $file) {
            $tree .= $prefix . "├── " . $file . "\n";
        }
        
        return $tree;
    }
    
    private function createExportIndex() 
    {
        $indexContent = $this->generateIndexHTML();
        file_put_contents($this->exportDir . '/index.html', $indexContent);
        echo "📋 Created export index file\n";
    }
    
    private function generateIndexHTML() 
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSYWX API 2025 - Wiki Export</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        .section {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .file-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        .file-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .file-item a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .file-item a:hover {
            text-decoration: underline;
        }
        .description {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .export-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 RSYWX API 2025 - Repository Wiki Export</h1>
        <p>Complete documentation and knowledge base export</p>
        <p><strong>Export Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
    </div>

    <div class="export-info">
        <strong>ℹ️ About This Export:</strong>
        This export contains all documentation, API specifications, configuration files, and knowledge base content from the RSYWX API 2025 repository. Use this for backup, sharing, or offline reference.
    </div>

    <div class="section">
        <h2>📖 API Documentation</h2>
        <div class="file-list">
            <div class="file-item">
                <a href="api-documentation/interactive-docs.html">Interactive API Documentation</a>
                <div class="description">Swagger UI with live API testing capabilities</div>
            </div>
            <div class="file-item">
                <a href="api-documentation/openapi-spec.json">OpenAPI Specification (JSON)</a>
                <div class="description">Machine-readable API specification</div>
            </div>
            <div class="file-item">
                <a href="api-documentation/openapi-spec.yaml">OpenAPI Specification (YAML)</a>
                <div class="description">Human-readable API specification</div>
            </div>
            <div class="file-item">
                <a href="api-documentation/api-documentation.md">API Documentation (Chinese)</a>
                <div class="description">Detailed API reference in Markdown format</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>📚 Project Documentation</h2>
        <div class="file-list">
            <div class="file-item">
                <a href="project-documentation/readme.md">Project README</a>
                <div class="description">Main project overview and setup guide</div>
            </div>
            <div class="file-item">
                <a href="project-documentation/project-structure.md">Project Structure</a>
                <div class="description">Complete directory structure and organization</div>
            </div>
            <div class="file-item">
                <a href="project-documentation/dependencies.json">Dependencies List</a>
                <div class="description">Complete list of project dependencies</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>🗄️ Database Schema</h2>
        <div class="file-list">
            <div class="file-item">
                <a href="database-schema/complete-schema.sql">Complete Database Schema</a>
                <div class="description">Full MySQL database structure and relationships</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>⚙️ Configuration Files</h2>
        <div class="file-list">
            <div class="file-item">
                <a href="configuration/environment-template.txt">Environment Template</a>
                <div class="description">Template for environment configuration</div>
            </div>
            <div class="file-item">
                <a href="configuration/apache-virtual-host.conf">Apache Virtual Host</a>
                <div class="description">Production Apache configuration</div>
            </div>
            <div class="file-item">
                <a href="configuration/git-ignore-rules.txt">Git Ignore Rules</a>
                <div class="description">Version control ignore patterns</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>🧪 Testing Documentation</h2>
        <div class="file-list">
            <div class="file-item">
                <a href="testing/test-summary.md">Test Summary</a>
                <div class="description">Overview of test coverage and results</div>
            </div>
            <div class="file-item">
                <a href="testing/phpunit-config.xml">PHPUnit Configuration</a>
                <div class="description">Testing framework configuration</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>🚀 Getting Started</h2>
        <ol>
            <li><strong>API Documentation:</strong> Start with the <a href="api-documentation/interactive-docs.html">Interactive API Documentation</a> for a complete overview</li>
            <li><strong>Setup Guide:</strong> Follow the <a href="project-documentation/readme.md">Project README</a> for installation instructions</li>
            <li><strong>Database:</strong> Import the <a href="database-schema/complete-schema.sql">database schema</a> to set up your environment</li>
            <li><strong>Configuration:</strong> Use the <a href="configuration/environment-template.txt">environment template</a> to configure your settings</li>
        </ol>
    </div>

    <footer style="text-align: center; margin-top: 40px; color: #666; font-size: 0.9em;">
        <p>📦 Repository Wiki Export | Generated on ' . date('Y-m-d H:i:s') . '</p>
        <p>🔗 RSYWX API 2025 - Personal Library Management System</p>
    </footer>
</body>
</html>';
    }
}

// Run the export if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $exporter = new WikiExporter();
    $exporter->export();
}
?>