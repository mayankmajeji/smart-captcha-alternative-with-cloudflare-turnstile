#!/bin/bash
# Comprehensive prefix migration script
# Changes: turnstilewp → smartct, TURNSTILEWP → SMARTCT, TurnstileWP → SmartCT

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PLUGIN_DIR"

echo "🔄 Starting comprehensive prefix migration"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. PHP Constants: TURNSTILEWP_ → SMARTCT_
echo "📝 Step 1/6: Updating PHP constants (TURNSTILEWP_ → SMARTCT_)..."
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./tests/*" -type f -exec sed -i '' 's/TURNSTILEWP_/SMARTCT_/g' {} \;

# 2. Options/Settings: turnstilewp_settings → smartct_settings  
echo "📝 Step 2/6: Updating WordPress options (turnstilewp_settings → smartct_settings)..."
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./tests/*" -type f -exec sed -i '' "s/'turnstilewp_settings'/'smartct_settings'/g" {} \;

# 3. Admin pages, hooks, actions: turnstilewp- → smartct-
echo "📝 Step 3/6: Updating hooks/actions (turnstilewp- → smartct-)..."
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./tests/*" -type f -exec sed -i '' 's/turnstilewp-/smartct-/g' {} \;

# 4. Functions/variables: turnstilewp → smartct (but NOT text domain)
echo "📝 Step 4/6: Updating functions/variables (turnstilewp → smartct, preserving text domain)..."
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./tests/*" -type f -exec sed -i '' 's/turnstilewp_/smartct_/g' {} \;
find . -name "*.js" -not -path "./node_modules/*" -type f -exec sed -i '' 's/turnstilewp/smartct/g' {} \;

# 5. CSS classes: .turnstilewp- → .smartct-
echo "📝 Step 5/6: Updating CSS classes (.turnstilewp- → .smartct-)..."
find . -name "*.scss" -o -name "*.css" -type f -exec sed -i '' 's/turnstilewp-/smartct-/g' {} \;
find . -name "*.scss" -o -name "*.css" -type f -exec sed -i '' 's/\.turnstilewp/\.smartct/g' {} \;

# 6. PHP Namespace: TurnstileWP → SmartCT
echo "📝 Step 6/6: Updating PHP namespace (TurnstileWP → SmartCT)..."
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./tests/*" -type f -exec sed -i '' 's/namespace TurnstileWP/namespace SmartCT/g' {} \;
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./tests/*" -type f -exec sed -i '' 's/use TurnstileWP\\/use SmartCT\\/g' {} \;
find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./tests/*" -type f -exec sed -i '' 's/\\TurnstileWP\\/\\SmartCT\\/g' {} \;

echo ""
echo "✅ Prefix migration complete!"
echo ""
echo "⚠️  IMPORTANT: You may need to:"
echo "   1. Recompile SCSS: gulp sass"
echo "   2. Clear PHP opcache if testing"
echo "   3. Run database migration on plugin activation"

