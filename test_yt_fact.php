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

echo "FACT: " . sparsha_t( 'FACT' ) . "\n";
echo "min read: " . sparsha_t( 'min read' ) . "\n";
echo "Visit Our YouTube Channel: " . sparsha_t( 'Visit Our YouTube Channel' ) . "\n";
echo "Watch more guest stories: " . sparsha_t( 'Watch more guest stories & Ayurvedic wisdom on our YouTube channel' ) . "\n";
