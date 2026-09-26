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

echo "Ayurvedic Journal: " . sparsha_t( 'Ayurvedic Journal' ) . "\n";
echo "Latest Articles: " . sparsha_t( 'Latest Articles' ) . "\n";
echo "ACF journal_label (253): " . get_field( 'journal_label', 253 ) . "\n";
echo "ACF journal_heading (253): " . get_field( 'journal_heading', 253 ) . "\n";
