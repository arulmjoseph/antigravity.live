<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

$slides_en = get_field( 'hero_slides', 7 );
$slides_ro = get_field( 'hero_slides', 253 );

echo "=== EN HERO SLIDES (ID 7) ===\n";
print_r( $slides_en );

echo "\n=== RO HERO SLIDES (ID 253) ===\n";
print_r( $slides_ro );
