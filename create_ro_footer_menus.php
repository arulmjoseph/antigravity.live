<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== CREATING ROMANIAN FOOTER MENUS ===\n";

// 1. Services RO Menu
$services_ro_name = 'Services Menu RO';
$services_ro_menu = wp_get_nav_menu_object( $services_ro_name );
if ( ! $services_ro_menu ) {
	$services_ro_id = wp_create_nav_menu( $services_ro_name );
	echo "Created '{$services_ro_name}' (ID {$services_ro_id})\n";
} else {
	$services_ro_id = $services_ro_menu->term_id;
	echo "'{$services_ro_name}' already exists (ID {$services_ro_id})\n";
}

// Add Romanian treatment items to Services RO Menu
$ro_treatments = array(
	262 => 'Terapie de Înfrumusețare Ayurvedică',
	257 => 'Consultanță Ayurvedică',
	260 => 'Cure și Terapii Ayurvedice',
	258 => 'Terapii prin Masaj Ayurvedic',
	261 => 'Îngrijire pentru Femei',
);

$existing_services_items = wp_get_nav_menu_items( $services_ro_id ) ?: array();
$existing_services_obj_ids = wp_list_pluck( $existing_services_items, 'object_id' );

$pos = 1;
foreach ( $ro_treatments as $post_id => $title ) {
	if ( ! in_array( (string) $post_id, $existing_services_obj_ids, true ) && ! in_array( (int) $post_id, $existing_services_obj_ids, true ) ) {
		wp_update_nav_menu_item( $services_ro_id, 0, array(
			'menu-item-title'     => $title,
			'menu-item-object-id' => $post_id,
			'menu-item-object'    => 'treatment',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $pos,
		) );
		echo "  Added treatment ID {$post_id} '{$title}' to Services RO Menu\n";
	} else {
		echo "  Treatment ID {$post_id} '{$title}' already in Services RO Menu\n";
	}
	$pos++;
}

// Set language of Services RO menu in Polylang if available
if ( function_exists( 'pll_set_term_language' ) ) {
	pll_set_term_language( $services_ro_id, 'ro' );
	echo "Set Polylang language 'ro' for Services RO Menu (term ID {$services_ro_id})\n";
}


// 2. Footer RO Menu
$footer_ro_name = 'Footer Menu RO';
$footer_ro_menu = wp_get_nav_menu_object( $footer_ro_name );
if ( ! $footer_ro_menu ) {
	$footer_ro_id = wp_create_nav_menu( $footer_ro_name );
	echo "\nCreated '{$footer_ro_name}' (ID {$footer_ro_id})\n";
} else {
	$footer_ro_id = $footer_ro_menu->term_id;
	echo "\n'{$footer_ro_name}' already exists (ID {$footer_ro_id})\n";
}

// Add Romanian page items to Footer RO Menu
$ro_pages = array(
	287 => 'Despre Noi',
	255 => 'Lista de Prețuri',
	256 => 'Vouchere Cadou',
	254 => 'Contactați-ne',
);

$existing_footer_items = wp_get_nav_menu_items( $footer_ro_id ) ?: array();
$existing_footer_obj_ids = wp_list_pluck( $existing_footer_items, 'object_id' );

$pos = 1;
foreach ( $ro_pages as $page_id => $title ) {
	if ( ! in_array( (string) $page_id, $existing_footer_obj_ids, true ) && ! in_array( (int) $page_id, $existing_footer_obj_ids, true ) ) {
		wp_update_nav_menu_item( $footer_ro_id, 0, array(
			'menu-item-title'     => $title,
			'menu-item-object-id' => $page_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $pos,
		) );
		echo "  Added page ID {$page_id} '{$title}' to Footer RO Menu\n";
	} else {
		echo "  Page ID {$page_id} '{$title}' already in Footer RO Menu\n";
	}
	$pos++;
}

if ( function_exists( 'pll_set_term_language' ) ) {
	pll_set_term_language( $footer_ro_id, 'ro' );
	echo "Set Polylang language 'ro' for Footer RO Menu (term ID {$footer_ro_id})\n";
}


// 3. Assign Polylang nav_menus location options
$theme = get_option( 'stylesheet' ); // sparsha-wp
$pll_opts = get_option( 'polylang', array() );

if ( ! isset( $pll_opts['nav_menus'][ $theme ] ) ) {
	$pll_opts['nav_menus'][ $theme ] = array();
}

$pll_opts['nav_menus'][ $theme ]['primary'] = array(
	'en' => 2,
	'ro' => 60,
);
$pll_opts['nav_menus'][ $theme ]['footer-services'] = array(
	'en' => 67,
	'ro' => $services_ro_id,
);
$pll_opts['nav_menus'][ $theme ]['footer'] = array(
	'en' => 3,
	'ro' => $footer_ro_id,
);

update_option( 'polylang', $pll_opts );
echo "\nUpdated Polylang nav_menus configuration:\n";
print_r( $pll_opts['nav_menus'][ $theme ] );

// Flush WP Cache
if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
echo "\nSUCCESS: Romanian footer menus created and assigned.\n";
