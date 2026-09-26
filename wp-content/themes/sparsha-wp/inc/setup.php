<?php

add_action( 'after_setup_theme', 'sparsha_theme_setup' );
function sparsha_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 90,
		'width'       => 370,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

	register_nav_menus( array(
		'primary'          => __( 'Primary Menu', 'sparsha-wp' ),
		'footer'           => __( 'Footer Menu', 'sparsha-wp' ),
		'footer-services'  => __( 'Footer — Services Column', 'sparsha-wp' ),
	) );

	add_image_size( 'sparsha-hero', 1920, 1080, true );
	add_image_size( 'sparsha-card', 600, 400, true );
}

/* ── Clean up <head>: Remove WP bloat, Emojis, Gutenberg CSS, and unused tags ── */

add_action( 'template_redirect', 'sparsha_strip_head_bloat' );
add_action( 'init', 'sparsha_strip_head_bloat' );
function sparsha_strip_head_bloat() {
	remove_action( 'wp_head', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
	add_filter( 'tiny_mce_plugins', function( $p ) { return is_array( $p ) ? array_diff( $p, array( 'wpemoji' ) ) : array(); } );

	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'wp_maybe_inline_styles', 1 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
}

// Prevent WordPress redirect_canonical loop on all XML sitemaps
add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
	if (
		isset( $_GET['sitemap'] ) ||
		( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'sitemap' ) !== false ) ||
		( $requested_url && strpos( $requested_url, 'sitemap' ) !== false ) ||
		( $redirect_url && strpos( $redirect_url, 'sitemap' ) !== false )
	) {
		return false;
	}
	return $redirect_url;
}, 10, 2 );

// Deregister & dequeue Gutenberg block styles & classic theme styles on frontend
function sparsha_remove_block_styles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'core-block-supports' );
	wp_dequeue_style( 'wp-emoji-styles' );

	wp_deregister_style( 'wp-block-library' );
	wp_deregister_style( 'wp-block-library-theme' );
	wp_deregister_style( 'classic-theme-styles' );
	wp_deregister_style( 'global-styles' );
	wp_deregister_style( 'core-block-supports' );
	wp_deregister_style( 'wp-emoji-styles' );
}
add_action( 'wp_enqueue_scripts', 'sparsha_remove_block_styles', 999 );

// Only load Contact Form 7 scripts & styles on pages that actually contain a contact form
add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() ) {
		return;
	}

	global $post;
	$has_form = false;

	if ( is_a( $post, 'WP_Post' ) ) {
		if (
			is_page_template( 'page-contact.php' ) ||
			( ! empty( $post->post_content ) && (
				has_shortcode( $post->post_content, 'contact-form-7' ) ||
				strpos( $post->post_content, 'wpcf7' ) !== false
			) )
		) {
			$has_form = true;
		}
	}

	if ( ! $has_form ) {
		wp_dequeue_style( 'contact-form-7' );
		wp_dequeue_script( 'contact-form-7' );
		wp_dequeue_script( 'wpcf7-recaptcha' );
	}
}, 999 );

// Defer non-critical JavaScript (main.js, swiper, contact form 7) to eliminate critical chain blocking
add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
	if ( is_admin() ) {
		return $tag;
	}
	if ( in_array( $handle, array( 'sparsha-main', 'swiper-js', 'contact-form-7', 'wpcf7-recaptcha' ), true ) ) {
		if ( strpos( $tag, 'defer' ) === false ) {
			return str_replace( ' src=', ' defer src=', $tag );
		}
	}
	return $tag;
}, 10, 3 );

// Load Font Awesome non-blockingly to eliminate critical path chain blocking
add_filter( 'style_loader_tag', function( $html, $handle, $href, $media ) {
	if ( is_admin() ) {
		return $html;
	}
	if ( 'font-awesome' === $handle ) {
		return "<link rel='stylesheet' id='font-awesome-css' href='" . esc_url( $href ) . "' media='print' onload=\"this.media='all'\"><noscript><link rel='stylesheet' href='" . esc_url( $href ) . "'></noscript>\n";
	}
	return $html;
}, 10, 4 );
add_action( 'wp_print_styles', 'sparsha_remove_block_styles', 999 );

// Remove all automated resource hints (preconnect / dns-prefetch) to solve Lighthouse warning
add_filter( 'wp_resource_hints', '__return_empty_array', 999 );

