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

echo "Years in Europe: " . sparsha_t( 'Years in Europe' ) . "\n";
echo "Rooted in Kerala, India: " . sparsha_t( 'Rooted in Kerala, India' ) . "\n";
echo "ACF about_badge_text (253): " . get_field( 'about_badge_text', 253 ) . "\n";
echo "ACF about_origin_tag (253): " . get_field( 'about_origin_tag', 253 ) . "\n";
