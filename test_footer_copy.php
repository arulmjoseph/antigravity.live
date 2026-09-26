<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

$_SERVER['REQUEST_URI'] = '/ro/';
if ( function_exists( 'PLL' ) ) {
	$ro = PLL()->model->get_language( 'ro' );
	if ( $ro ) {
		PLL()->curlang                = $ro;
		$GLOBALS['polylang']->curlang = $ro;
	}
}

$copy = get_theme_mod( 'sparsha_copyright', '© 2025 Sparsha Ayurveda Centre · Bucharest, Romania. All rights reserved.' );
echo "Original Theme Mod: " . $copy . "\n";
echo "Translated sparsha_t: " . sparsha_t( $copy ) . "\n";
