<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

$filepath = __DIR__ . '/wp-content/themes/sparsha-wp/page-home.php';
$content  = file_get_contents( $filepath );

$search  = '<p class="text-[9px] font-semibold tracking-[0.1em] uppercase mt-0.5">Years</p>';
$replace = '<p class="text-[9px] font-semibold tracking-[0.1em] uppercase mt-0.5"><?php echo esc_html( sparsha_t( \'Years\' ) ); ?></p>';

if ( strpos( $content, $search ) !== false ) {
	$content = str_replace( $search, $replace, $content );
	file_put_contents( $filepath, $content );
	echo "UPDATED PAGE HOME\n";
} else {
	echo "SEARCH NOT FOUND IN PAGE HOME\n";
}
