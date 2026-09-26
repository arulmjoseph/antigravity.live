<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== SEEDING HERO SLIDE CTA BUTTONS ===\n";

// English Home Page ID 7
$slides_en = get_field( 'hero_slides', 7 );
if ( is_array( $slides_en ) && ! empty( $slides_en ) ) {
	foreach ( $slides_en as &$s ) {
		$s['cta_primary'] = array(
			'title'  => 'Book Appointment',
			'url'    => 'https://sparsha.cmsworks.net/contact/',
			'target' => '',
		);
		$s['cta_secondary'] = array(
			'title'  => 'Explore Services',
			'url'    => 'https://sparsha.cmsworks.net/treatments/',
			'target' => '',
		);
	}
	update_field( 'hero_slides', $slides_en, 7 );
	echo "Updated hero_slides for EN Home Page ID 7.\n";
}

// Romanian Home Page ID 253
$slides_ro = get_field( 'hero_slides', 253 );
if ( is_array( $slides_ro ) && ! empty( $slides_ro ) ) {
	foreach ( $slides_ro as &$s ) {
		$s['cta_primary'] = array(
			'title'  => 'Rezervați o Programare',
			'url'    => 'https://sparsha.cmsworks.net/ro/contactati-ne/',
			'target' => '',
		);
		$s['cta_secondary'] = array(
			'title'  => 'Explorați Serviciile',
			'url'    => 'https://sparsha.cmsworks.net/ro/treatments/',
			'target' => '',
		);
	}
	update_field( 'hero_slides', $slides_ro, 253 );
	echo "Updated hero_slides for RO Home Page ID 253.\n";
}
