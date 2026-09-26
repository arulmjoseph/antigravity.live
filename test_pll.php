<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== TESTING POLYLANG STRING TRANSLATIONS (RO) ===\n";

if ( function_exists( 'PLL' ) ) {
	$ro_lang = PLL()->model->get_language( 'ro' );
	if ( $ro_lang ) {
		PLL()->curlang = $ro_lang;
		$GLOBALS['polylang']->curlang = $ro_lang;
	}
}

echo "Current language slug: " . ( function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : 'none' ) . "\n";
echo "pll__('Book Your Appointment'): " . pll__( 'Book Your Appointment' ) . "\n";
echo "pll__('Book Your Appointment Over the Phone'): " . pll__( 'Book Your Appointment Over the Phone' ) . "\n";
echo "pll__('Contact Us'): " . pll__( 'Contact Us' ) . "\n";
echo "pll__('Explore'): " . pll__( 'Explore' ) . "\n";
echo "pll__('Authentic Ayurvedic...'): " . pll__( 'Authentic Ayurvedic healing rooted in the traditions of Kerala, India — bringing natural wellness to the heart of Europe since 2012.' ) . "\n";
echo "pll__('Bd. Pipera...'): " . pll__( 'Bd. Pipera nr. 1-VIII D, Voluntari, Romania' ) . "\n";
