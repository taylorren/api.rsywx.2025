#!/bin/bash

# Repository Wiki Archive Creator
# Creates a portable archive of all wiki and documentation content

echo "📦 Creating Repository Wiki Archive..."
echo ""

# Set variables
PROJECT_NAME="rsywx-api-2025"
ARCHIVE_NAME="${PROJECT_NAME}-wiki-$(date +%Y%m%d-%H%M%S)"
TEMP_DIR="/tmp/${ARCHIVE_NAME}"

# Create temporary directory
mkdir -p "$TEMP_DIR"

echo "📁 Preparing archive content..."

# Copy wiki export directory
if [ -d "wiki-export" ]; then
    cp -r wiki-export "$TEMP_DIR/"
    echo "  ✓ Copied organized wiki export"
fi

# Copy consolidated exports
for file in COMPLETE_WIKI.md COMPLETE_WIKI.html COMPLETE_WIKI.txt; do
    if [ -f "$file" ]; then
        cp "$file" "$TEMP_DIR/"
        echo "  ✓ Copied $file"
    fi
done

# Copy key documentation files
echo "📚 Including key documentation files..."
KEY_FILES=(
    "README.md"
    "API_DOCUMENTATION.md"
    "composer.json"
    "phpunit.xml"
    "schema.rsywx.sql"
    ".env.example"
    "apache-vhost.conf"
    ".gitignore"
)

for file in "${KEY_FILES[@]}"; do
    if [ -f "$file" ]; then
        cp "$file" "$TEMP_DIR/"
        echo "  ✓ Included $file"
    fi
done

# Copy API documentation files
echo "🌐 Including API documentation..."
if [ -d "public" ]; then
    mkdir -p "$TEMP_DIR/api-docs"
    cp public/api-docs.* "$TEMP_DIR/api-docs/" 2>/dev/null || true
    cp public/index.php "$TEMP_DIR/api-docs/" 2>/dev/null || true
    echo "  ✓ Included API documentation files"
fi

# Copy test documentation
echo "🧪 Including test documentation..."
if [ -d "tests" ]; then
    mkdir -p "$TEMP_DIR/tests"
    find tests -name "*.md" -exec cp {} "$TEMP_DIR/tests/" \; 2>/dev/null || true
    cp phpunit.xml "$TEMP_DIR/tests/" 2>/dev/null || true
    echo "  ✓ Included test documentation"
fi

# Create archive info file
echo "📋 Creating archive information..."
cat > "$TEMP_DIR/ARCHIVE_INFO.txt" << EOF
RSYWX API 2025 - Repository Wiki Archive
========================================

Archive Created: $(date)
Project: Personal Library Management API
Technology: PHP + Slim Framework

Contents:
---------
- wiki-export/          : Organized documentation export
- COMPLETE_WIKI.*       : Consolidated documentation in multiple formats
- api-docs/             : OpenAPI specifications and interactive docs
- tests/                : Testing documentation
- Key configuration files and documentation

Getting Started:
---------------
1. Open 'wiki-export/index.html' for organized browsing
2. Read 'COMPLETE_WIKI.html' for consolidated documentation
3. Check 'README.md' for project setup instructions
4. Review 'API_DOCUMENTATION.md' for API details

Archive Structure:
-----------------
$(find "$TEMP_DIR" -type f | sort | sed 's|'$TEMP_DIR'/||' | head -20)
$([ $(find "$TEMP_DIR" -type f | wc -l) -gt 20 ] && echo "... and $(( $(find "$TEMP_DIR" -type f | wc -l) - 20 )) more files")

Total Files: $(find "$TEMP_DIR" -type f | wc -l)
Total Size: $(du -sh "$TEMP_DIR" | cut -f1)
EOF

echo "  ✓ Created archive information file"

# Create the archive
echo ""
echo "🗜️  Creating compressed archive..."

# Try different compression methods
if command -v tar >/dev/null 2>&1; then
    # Create tar.gz archive
    cd /tmp
    tar -czf "${ARCHIVE_NAME}.tar.gz" "${ARCHIVE_NAME}"
    ARCHIVE_PATH="/tmp/${ARCHIVE_NAME}.tar.gz"
    ARCHIVE_TYPE="tar.gz"
    echo "  ✓ Created tar.gz archive"
elif command -v zip >/dev/null 2>&1; then
    # Create zip archive
    cd /tmp
    zip -r "${ARCHIVE_NAME}.zip" "${ARCHIVE_NAME}" >/dev/null
    ARCHIVE_PATH="/tmp/${ARCHIVE_NAME}.zip"
    ARCHIVE_TYPE="zip"
    echo "  ✓ Created zip archive"
else
    # Fallback: just move the directory
    ARCHIVE_PATH="/tmp/${ARCHIVE_NAME}"
    ARCHIVE_TYPE="directory"
    echo "  ⚠️  No compression available, created directory"
fi

# Move archive to project directory
if [ "$ARCHIVE_TYPE" != "directory" ]; then
    mv "$ARCHIVE_PATH" .
    FINAL_PATH="./$(basename "$ARCHIVE_PATH")"
else
    mv "$TEMP_DIR" .
    FINAL_PATH="./$(basename "$TEMP_DIR")"
fi

# Clean up
[ -d "$TEMP_DIR" ] && rm -rf "$TEMP_DIR"

# Display results
echo ""
echo "✅ Wiki archive created successfully!"
echo ""
echo "📦 Archive Details:"
echo "   Name: $(basename "$FINAL_PATH")"
echo "   Type: $ARCHIVE_TYPE"
echo "   Location: $FINAL_PATH"
echo "   Size: $(du -sh "$FINAL_PATH" | cut -f1)"
echo ""
echo "🚀 Usage:"
echo "   • Extract and open 'wiki-export/index.html' for browsing"
echo "   • Share this archive for complete project documentation"
echo "   • Use for backup or offline reference"
echo ""