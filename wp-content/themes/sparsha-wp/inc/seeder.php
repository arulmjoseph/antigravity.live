<?php
/**
 * Theme Page & Menu Seeder
 * Run once: wp eval-file inc/seeder.php
 * Safe to re-run — skips existing pages and menus.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// --- PAGES ---
$pages = [
	[ 'Home',           'home',           'page-home.php',           true  ],
	[ 'About',          'about',          'page-about.php',          false ],
	[ 'Service',        'service',        'page-service.php',        false ],
	[ 'Pricelist',      'pricelist',      'page-pricelist.php',      false ],
	[ 'Contact',        'contact',        'page-contact.php',        false ],
	[ 'Gift Vouchers',  'gift-vouchers',  'page-gift-vouchers.php',  false ],
];

$front_page_id = 0;

foreach ( $pages as $p ) {
	[ $title, $slug, $template, $is_front ] = $p;

	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $existing ) {
		$page_id = $existing->ID;
		WP_CLI::log( "Skipped (exists): {$title} (ID {$page_id})" );
	} else {
		$page_id = wp_insert_post( [
			'post_title'  => $title,
			'post_name'   => $slug,
			'post_type'   => 'page',
			'post_status' => 'publish',
		] );
		WP_CLI::log( "Created: {$title} (ID {$page_id})" );
	}

	update_post_meta( $page_id, '_wp_page_template', $template );

	if ( $is_front ) {
		$front_page_id = $page_id;
	}
}

if ( $front_page_id ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $front_page_id );
	WP_CLI::log( "Front page set to ID {$front_page_id}" );
}

// --- MENUS ---
$locations = get_nav_menu_locations();

$primary_menu = wp_get_nav_menu_object( 'Primary Menu' );
if ( ! $primary_menu ) {
	$primary_id = wp_create_nav_menu( 'Primary Menu' );
	WP_CLI::log( "Created Primary Menu (ID {$primary_id})" );
} else {
	$primary_id = $primary_menu->term_id;
	WP_CLI::log( "Primary Menu exists (ID {$primary_id})" );
}

$footer_menu = wp_get_nav_menu_object( 'Footer Menu' );
if ( ! $footer_menu ) {
	$footer_id = wp_create_nav_menu( 'Footer Menu' );
	WP_CLI::log( "Created Footer Menu (ID {$footer_id})" );
} else {
	$footer_id = $footer_menu->term_id;
	WP_CLI::log( "Footer Menu exists (ID {$footer_id})" );
}

// Primary menu pages (nav order from HTML)
$primary_pages = [ 'about', 'service', 'pricelist', 'contact' ];
foreach ( $primary_pages as $i => $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		$existing_items = wp_get_nav_menu_items( $primary_id );
		$already_added  = false;
		if ( $existing_items ) {
			foreach ( $existing_items as $item ) {
				if ( (int) $item->object_id === $page->ID ) {
					$already_added = true;
					break;
				}
			}
		}
		if ( ! $already_added ) {
			wp_update_nav_menu_item( $primary_id, 0, [
				'menu-item-title'     => $page->post_title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $i + 1,
			] );
			WP_CLI::log( "Added to primary menu: {$page->post_title}" );
		}
	}
}

// Footer menu pages
$footer_pages = [ 'about', 'pricelist', 'gift-vouchers', 'contact' ];
foreach ( $footer_pages as $i => $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		$existing_items = wp_get_nav_menu_items( $footer_id );
		$already_added  = false;
		if ( $existing_items ) {
			foreach ( $existing_items as $item ) {
				if ( (int) $item->object_id === $page->ID ) {
					$already_added = true;
					break;
				}
			}
		}
		if ( ! $already_added ) {
			wp_update_nav_menu_item( $footer_id, 0, [
				'menu-item-title'     => $page->post_title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $i + 1,
			] );
			WP_CLI::log( "Added to footer menu: {$page->post_title}" );
		}
	}
}

$locations['primary'] = $primary_id;
$locations['footer']  = $footer_id;
set_theme_mod( 'nav_menu_locations', $locations );
WP_CLI::log( 'Menu locations assigned.' );

WP_CLI::success( 'Seeder complete.' );
