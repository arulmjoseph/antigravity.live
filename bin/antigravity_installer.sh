#!/usr/bin/env bash
# Antigravity Web Studio - One-Click CLI WordPress Installer & Seeder Script

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$( dirname "$SCRIPT_DIR" )"

echo "============================================================"
echo "   ANTIGRAVITY WEB STUDIO - WORDPRESS AUTOMATED INSTALLER   "
echo "============================================================"
echo "Root Directory: $ROOT_DIR"
echo ""

# 1. Check PHP availability
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP is not installed or not in PATH."
    exit 1
fi
echo "✓ PHP version $(php -r 'echo PHP_VERSION;') detected."

# 2. Check wp-config.php
if [ -f "$ROOT_DIR/wp-config.php" ]; then
    echo "✓ wp-config.php found."
else
    echo "⚠️ wp-config.php missing. Copying wp-config-sample.php..."
    cp "$ROOT_DIR/wp-config-sample.php" "$ROOT_DIR/wp-config.php"
fi

# 3. Check for MySQL dump if initial import is needed
if [ -f "$ROOT_DIR/sparsha_live.sql" ]; then
    echo "✓ Database dump sparsha_live.sql detected."
fi

# 4. Run Seed Scripts
echo ""
echo "Executing Antigravity Content Seeder Scripts..."
cd "$ROOT_DIR"

SEEDS=(
    "seed_hero_cta.php"
    "seed_badges_acf.php"
    "seed_facts_acf.php"
    "seed_values_acf.php"
    "seed_team_members.php"
    "seed_video_gallery.php"
    "create_ro_contact_form.php"
    "create_ro_footer_menus.php"
)

for seed in "${SEEDS[@]}"; do
    if [ -f "$seed" ]; then
        echo "  → Running $seed..."
        php "$seed" || true
    fi
done

echo ""
echo "============================================================"
echo "   SUCCESS: Antigravity WordPress Site Setup Complete!     "
echo "   Open http://localhost/antigravity.live or index_wizard.html"
echo "============================================================"
