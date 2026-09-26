<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== SEEDING ABOUT VALUES SECTION ACF FIELDS ===\n";

// English About Page ID 8
update_field( 'values_label', 'What We Stand For', 8 );
update_field( 'values_heading', 'Our Core Values', 8 );
echo "Updated EN About Page ID 8.\n";

// Romanian About Page ID 287
update_field( 'values_label', 'Ce Reprezentăm', 287 );
update_field( 'values_heading', 'Valorile Noastre Fundamentale', 287 );
echo "Updated RO About Page ID 287.\n";
