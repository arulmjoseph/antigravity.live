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

echo "Years of Healing: " . sparsha_t( 'Years of Healing' ) . "\n";
echo "Years in Europe: " . sparsha_t( 'Years in Europe' ) . "\n";
echo "Years: " . sparsha_t( 'Years' ) . "\n";
echo "View All: " . sparsha_t( 'View All' ) . "\n";
