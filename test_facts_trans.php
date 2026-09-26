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

echo "Ancient Ayurvedic Wisdom: " . sparsha_t( 'Ancient Ayurvedic Wisdom' ) . "\n";
echo "Interesting Facts: " . sparsha_t( 'Interesting Facts' ) . "\n";
echo "Rooted in Wisdom: " . sparsha_t( 'Rooted in Wisdom. Made for You.' ) . "\n";
