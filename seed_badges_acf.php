<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== SEEDING ABOUT BADGES ACF FIELDS ===\n";

// Romanian Home Page ID 253
update_field( 'about_badge_text', 'Ani în Europa', 253 );
update_field( 'about_origin_tag', 'Cu rădăcini în Kerala, India', 253 );
echo "Updated RO Home Page ID 253.\n";

// English Home Page ID 7
update_field( 'about_badge_text', 'Years in Europe', 7 );
update_field( 'about_origin_tag', 'Rooted in Kerala, India', 7 );
echo "Updated EN Home Page ID 7.\n";
