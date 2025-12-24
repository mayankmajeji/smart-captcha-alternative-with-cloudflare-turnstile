#!/bin/bash
# Migration script to update all prefixes from tswp_ to smartct_
# This script updates the codebase only, not the database

echo "🔄 Starting prefix migration: tswp_ → smartct_"
echo ""

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PLUGIN_DIR"

# Count before
BEFORE_COUNT=$(grep -r "tswp_" includes/ assets/ --include="*.php" --include="*.js" 2>/dev/null | wc -l | tr -d ' ')
echo "📊 Found $BEFORE_COUNT instances of 'tswp_' to replace"
echo ""

# Replace in PHP files
echo "📝 Updating PHP files..."
find includes/ -name "*.php" -type f -exec sed -i '' 's/tswp_/smartct_/g' {} \;

# Replace in JS files  
echo "📝 Updating JavaScript files..."
find assets/ -name "*.js" -type f -exec sed -i '' 's/tswp_/smartct_/g' {} \;

# Count after
AFTER_COUNT=$(grep -r "tswp_" includes/ assets/ --include="*.php" --include="*.js" 2>/dev/null | wc -l | tr -d ' ')

echo ""
echo "✅ Migration complete!"
echo "📊 Remaining instances: $AFTER_COUNT"
echo ""
echo "⚠️  IMPORTANT: Database options still use 'tswp_' prefix"
echo "    A database migration will run automatically on plugin activation"

