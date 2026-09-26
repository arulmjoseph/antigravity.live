<?php

function sparsha_register_treatment_cpt() {
	$labels = array(
		'name'               => __( 'Treatments', 'sparsha-wp' ),
		'singular_name'      => __( 'Treatment', 'sparsha-wp' ),
		'add_new'            => __( 'Add New Treatment', 'sparsha-wp' ),
		'add_new_item'       => __( 'Add New Treatment', 'sparsha-wp' ),
		'edit_item'          => __( 'Edit Treatment', 'sparsha-wp' ),
		'new_item'           => __( 'New Treatment', 'sparsha-wp' ),
		'view_item'          => __( 'View Treatment', 'sparsha-wp' ),
		'search_items'       => __( 'Search Treatments', 'sparsha-wp' ),
		'not_found'          => __( 'No treatments found', 'sparsha-wp' ),
		'not_found_in_trash' => __( 'No treatments in Trash', 'sparsha-wp' ),
		'menu_name'          => __( 'Treatments', 'sparsha-wp' ),
	);

	register_post_type( 'treatment', array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'treatments', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => true,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-heart',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes' ),
	) );
}
add_action( 'init', 'sparsha_register_treatment_cpt' );

function sparsha_register_treatment_category() {
	register_taxonomy( 'treatment_category', 'treatment', array(
		'labels' => array(
			'name'          => __( 'Treatment Categories', 'sparsha-wp' ),
			'singular_name' => __( 'Category', 'sparsha-wp' ),
			'add_new_item'  => __( 'Add New Category', 'sparsha-wp' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'treatment-category', 'with_front' => false ),
	) );
}
add_action( 'init', 'sparsha_register_treatment_category' );
