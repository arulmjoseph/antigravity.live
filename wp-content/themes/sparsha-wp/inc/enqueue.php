<?php

// Inline local font definitions & preload critical font files in <head>
add_action( 'wp_head', 'sparsha_preload_and_inline_fonts', 1 );
function sparsha_preload_and_inline_fonts() {
	$uri = get_template_directory_uri();
	// Preload critical Latin fonts to eliminate font chain latency
	echo '<link rel="preload" href="' . esc_url( $uri . '/assets/fonts/font_20.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<link rel="preload" href="' . esc_url( $uri . '/assets/fonts/font_16.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<link rel="preload" href="' . esc_url( $uri . '/assets/fonts/font_3.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<link rel="preload" href="' . esc_url( $uri . '/assets/fonts/font_8.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";

	$fonts_path = get_template_directory() . '/assets/css/fonts.css';
	if ( file_exists( $fonts_path ) ) {
		$css = file_get_contents( $fonts_path );
		$css = str_replace( '../fonts/', $uri . '/assets/fonts/', $css );
		echo '<style id="sparsha-local-fonts">' . $css . '</style>' . "\n";
	}
}

add_action( 'wp_enqueue_scripts', 'sparsha_enqueue_assets' );
function sparsha_enqueue_assets() {
	$ver = wp_get_theme()->get( 'Version' );
	$uri = get_template_directory_uri();

	// 1. Production Compiled Tailwind CSS
	$tw_path = get_template_directory() . '/assets/css/tailwind.min.css';
	$tw_ver  = file_exists( $tw_path ) ? filemtime( $tw_path ) : $ver;
	wp_enqueue_style( 'sparsha-tailwind', $uri . '/assets/css/tailwind.min.css', array(), $tw_ver );

	// 2. Swiper 11 (Self-hosted locally for home & panchakarma retreat pages)
	if ( is_page_template( 'page-home.php' ) || is_page_template( 'page-authentic-panchakarma-retreat.php' ) ) {
		$sw_css = get_template_directory() . '/assets/css/swiper-bundle.min.css';
		$sw_js  = get_template_directory() . '/assets/js/swiper-bundle.min.js';
		wp_enqueue_style( 'swiper-css', $uri . '/assets/css/swiper-bundle.min.css', array(), file_exists( $sw_css ) ? filemtime( $sw_css ) : $ver );
		wp_enqueue_script( 'swiper-js', $uri . '/assets/js/swiper-bundle.min.js', array(), file_exists( $sw_js ) ? filemtime( $sw_js ) : $ver, true );
	}

	// 3. Font Awesome 6 (Self-hosted locally — 0 third party requests)
	$fa_path = get_template_directory() . '/assets/css/fontawesome.min.css';
	$fa_ver  = file_exists( $fa_path ) ? filemtime( $fa_path ) : $ver;
	wp_enqueue_style( 'font-awesome', $uri . '/assets/css/fontawesome.min.css', array(), $fa_ver );

	// 4. Theme Main CSS
	$css_path = get_template_directory() . '/assets/css/style.css';
	$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : $ver;
	wp_enqueue_style( 'sparsha-theme', $uri . '/assets/css/style.css', array(), $css_ver );

	// 5. Theme Main JS
	$js_path = get_template_directory() . '/assets/js/main.js';
	$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : $ver;
	wp_enqueue_script( 'sparsha-main', $uri . '/assets/js/main.js', array(), $js_ver, true );
}
