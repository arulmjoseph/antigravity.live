<?php

add_action( 'widgets_init', 'sparsha_register_widget_areas' );
function sparsha_register_widget_areas() {
	$footer_columns = array(
		'footer-1' => __( 'Footer Column 1', 'sparsha-wp' ),
		'footer-2' => __( 'Footer Column 2', 'sparsha-wp' ),
		'footer-3' => __( 'Footer Column 3', 'sparsha-wp' ),
		'footer-4' => __( 'Footer Column 4', 'sparsha-wp' ),
	);

	foreach ( $footer_columns as $id => $name ) {
		register_sidebar( array(
			'name'          => $name,
			'id'            => $id,
			'before_widget' => '<div id="%1$s" class="widget %2$s mb-6">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="text-amber-400 text-xs font-semibold tracking-[0.18em] uppercase mb-5">',
			'after_title'   => '</h4>',
		) );
	}
}
