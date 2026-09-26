<?php

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/widgets.php';
require_once get_template_directory() . '/inc/cpt.php';
require_once get_template_directory() . '/inc/acf-fields.php';
require_once get_template_directory() . '/inc/acf-seed.php';
require_once get_template_directory() . '/inc/polylang.php';
require_once get_template_directory() . '/inc/woocommerce.php';

add_action( 'pre_get_posts', function( $query ) {
	if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'treatment' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}
} );

/**
 * Resolve the banner image URL for the current (or given) post.
 * Priority: ACF page_banner_image → Customizer default → ''.
 */
function sparsha_page_banner_url( $post_id = null, $size = 'full' ) {
	$post_id = $post_id ?: get_queried_object_id();

	if ( function_exists( 'get_field' ) ) {
		$img = get_field( 'page_banner_image', $post_id );
		if ( is_array( $img ) ) {
			return isset( $img['sizes'][ $size ] ) ? $img['sizes'][ $size ] : $img['url'];
		}
		if ( is_numeric( $img ) ) {
			$src = wp_get_attachment_image_src( $img, $size );
			if ( $src ) return $src[0];
		}
	}

	$default_id = absint( get_theme_mod( 'sparsha_default_banner', 0 ) );
	if ( $default_id ) {
		$src = wp_get_attachment_image_src( $default_id, $size );
		if ( $src ) return $src[0];
	}

	return '';
}

add_filter( 'acf/settings/save_json', function() {
	return get_template_directory() . '/acf-json';
} );
add_filter( 'acf/settings/load_json', function( $paths ) {
	$paths[] = get_template_directory() . '/acf-json';
	return $paths;
} );
