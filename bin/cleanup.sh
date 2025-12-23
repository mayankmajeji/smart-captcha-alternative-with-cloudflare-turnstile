#!/bin/bash

# Cleanup script for Smart Cloudflare Turnstile Plugin
# Removes development and system files before WordPress.org submission

echo "🧹 Cleaning up plugin directory..."

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PLUGIN_DIR" || exit 1

# Remove .DS_Store files
echo "  → Removing .DS_Store files..."
find . -type f -name ".DS_Store" -delete

# Remove __MACOSX folders
echo "  → Removing __MACOSX folders..."
find . -type d -name "__MACOSX" -exec rm -rf {} + 2>/dev/null || true

# Remove Thumbs.db files
echo "  → Removing Thumbs.db files..."
find . -type f -name "Thumbs.db" -delete

# Remove editor backup files
echo "  → Removing editor backup files..."
find . -type f \( -name "*~" -o -name "*.swp" -o -name "*.swo" \) -delete

# Count remaining hidden files
HIDDEN_COUNT=$(find . -type f -name ".*" ! -path "*/.git/*" ! -name ".distignore" ! -name ".gitignore" ! -name ".gitattributes" ! -name ".editorconfig" ! -name ".npmrc" ! -name ".nvmrc" ! -name ".prettierrc" ! -name ".htmlhintrc" ! -name ".stylelintrc.json" ! -name ".eslintrc.js" | wc -l | tr -d ' ')

echo ""
echo "✅ Cleanup complete!"
echo "   Hidden system files remaining: $HIDDEN_COUNT"

if [ "$HIDDEN_COUNT" -gt 0 ]; then
    echo ""
    echo "⚠️  Warning: Found $HIDDEN_COUNT hidden file(s):"
    find . -type f -name ".*" ! -path "*/.git/*" ! -name ".distignore" ! -name ".gitignore" ! -name ".gitattributes" ! -name ".editorconfig" ! -name ".npmrc" ! -name ".nvmrc" ! -name ".prettierrc" ! -name ".htmlhintrc" ! -name ".stylelintrc.json" ! -name ".eslintrc.js"
fi

echo ""
echo "🎯 Ready for WordPress.org submission!"

