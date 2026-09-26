<?php

// One-time contact-info reset. Trigger: /?reset_contact=1 as admin.
add_action( 'init', function() {
	if ( ! isset( $_GET['reset_contact'] ) || $_GET['reset_contact'] !== '1' ) return;
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Admin only.' );
	set_theme_mod( 'sparsha_phone',         '+40 737 780 780' );
	set_theme_mod( 'sparsha_phone_href',    '+40737780780' );
	set_theme_mod( 'sparsha_phone_2',       '' );
	set_theme_mod( 'sparsha_phone_2_href',  '' );
	set_theme_mod( 'sparsha_whatsapp',      '+40 737 780 780' );
	set_theme_mod( 'sparsha_whatsapp_href', '40737780780' );
	set_theme_mod( 'sparsha_email',         '' );
	set_theme_mod( 'sparsha_address',       'Bd. Pipera nr. 1-VIII D, Voluntari, Romania' );
	set_theme_mod( 'sparsha_address_short', 'Bd. Pipera nr. 1-VIII D, Voluntari' );
	set_theme_mod( 'sparsha_float_phone',    '+40737780780' );
	set_theme_mod( 'sparsha_float_whatsapp', '40737780780' );
	wp_die( 'Contact info reset to Romania.' );
} );

add_action( 'customize_register', 'sparsha_customizer' );
function sparsha_customizer( $wp_customize ) {

	$bool_sanitize = function( $val ) { return (bool) $val; };

	// ── Panel: Theme Options ──
	$wp_customize->add_panel( 'sparsha_panel', array(
		'title'    => __( 'Theme Options', 'sparsha-wp' ),
		'priority' => 30,
	) );

	// ═══════════════════════════════════════
	// 1. TOP BAR
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_top_bar', array(
		'title' => __( 'Top Bar', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$wp_customize->add_setting( 'sparsha_top_bar_enable', array( 'default' => false, 'sanitize_callback' => $bool_sanitize ) );
	$wp_customize->add_control( 'sparsha_top_bar_enable', array( 'label' => __( 'Enable Top Bar', 'sparsha-wp' ), 'section' => 'sparsha_top_bar', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'sparsha_top_bar_left', array( 'default' => 'Authentic Ayurveda · Budapest, Hungary', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_top_bar_left', array( 'label' => __( 'Top Bar Left Text', 'sparsha-wp' ), 'section' => 'sparsha_top_bar', 'type' => 'text' ) );

	$wp_customize->add_setting( 'sparsha_top_bar_right', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_top_bar_right', array( 'label' => __( 'Top Bar Right Text', 'sparsha-wp' ), 'section' => 'sparsha_top_bar', 'type' => 'text' ) );

	$wp_customize->add_setting( 'top_bar_bg_color', array( 'default' => '#1a3a10', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'top_bar_bg_color', array( 'label' => __( 'Top Bar Background', 'sparsha-wp' ), 'section' => 'sparsha_top_bar' ) ) );

	$wp_customize->add_setting( 'top_bar_text_color', array( 'default' => '#fdf8f0', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'top_bar_text_color', array( 'label' => __( 'Top Bar Text Color', 'sparsha-wp' ), 'section' => 'sparsha_top_bar' ) ) );

	// ═══════════════════════════════════════
	// 2. BRANDING
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_branding', array(
		'title' => __( 'Branding', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$wp_customize->add_setting( 'sparsha_show_site_title', array( 'default' => false, 'sanitize_callback' => $bool_sanitize ) );
	$wp_customize->add_control( 'sparsha_show_site_title', array( 'label' => __( 'Show Site Title', 'sparsha-wp' ), 'section' => 'sparsha_branding', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'sparsha_show_tagline', array( 'default' => false, 'sanitize_callback' => $bool_sanitize ) );
	$wp_customize->add_control( 'sparsha_show_tagline', array( 'label' => __( 'Show Tagline', 'sparsha-wp' ), 'section' => 'sparsha_branding', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'header_logo_width_desktop', array( 'default' => 160, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'header_logo_width_desktop', array( 'label' => __( 'Header Logo Width — Desktop (px)', 'sparsha-wp' ), 'description' => __( 'Logo width in pixels for desktop screens (≥ 768px).', 'sparsha-wp' ), 'section' => 'sparsha_branding', 'type' => 'number', 'input_attrs' => array( 'min' => 40, 'max' => 400 ) ) );

	$wp_customize->add_setting( 'header_logo_width_mobile', array( 'default' => 120, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'header_logo_width_mobile', array( 'label' => __( 'Header Logo Width — Mobile (px)', 'sparsha-wp' ), 'description' => __( 'Logo width in pixels for mobile screens (< 768px).', 'sparsha-wp' ), 'section' => 'sparsha_branding', 'type' => 'number', 'input_attrs' => array( 'min' => 40, 'max' => 300 ) ) );

	$wp_customize->add_setting( 'footer_logo_width_desktop', array( 'default' => 140, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'footer_logo_width_desktop', array( 'label' => __( 'Footer Logo Width — Desktop (px)', 'sparsha-wp' ), 'section' => 'sparsha_branding', 'type' => 'number', 'input_attrs' => array( 'min' => 40, 'max' => 400 ) ) );

	$wp_customize->add_setting( 'footer_logo_width_mobile', array( 'default' => 100, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'footer_logo_width_mobile', array( 'label' => __( 'Footer Logo Width — Mobile (px)', 'sparsha-wp' ), 'section' => 'sparsha_branding', 'type' => 'number', 'input_attrs' => array( 'min' => 40, 'max' => 300 ) ) );

	// ── Default Page Banner ──
	$wp_customize->add_setting( 'sparsha_default_banner', array( 'default' => '', 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'sparsha_default_banner', array(
		'label'       => __( 'Default Page Banner Image', 'sparsha-wp' ),
		'description' => __( 'Used on all page banners unless overridden per-page (ACF: Page Banner Image). Recommended 1920×720.', 'sparsha-wp' ),
		'section'     => 'sparsha_branding',
		'mime_type'   => 'image',
	) ) );

	// ═══════════════════════════════════════
	// 3. COLORS
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_colors', array(
		'title' => __( 'Colors', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$colors = array(
		'primary_color'      => array( 'Primary Color', '#285a14' ),
		'secondary_color'    => array( 'Secondary Color', '#347319' ),
		'accent_color'       => array( 'Accent Color', '#f59e0b' ),
		'text_color'         => array( 'Text Color', '#2c1810' ),
		'bg_color'           => array( 'Background Color', '#fdf8f0' ),
		'heading_color'      => array( 'Heading Color', '#1a3a10' ),
		'border_color'       => array( 'Border Color', '#ddefd3' ),
		'header_bg_color'    => array( 'Header Background', '#ffffff' ),
		'header_text_color'  => array( 'Header Text Color', '#1a3a10' ),
		'footer_bg_color'    => array( 'Footer Background', '#1a3a10' ),
		'footer_text_color'  => array( 'Footer Text Color', '#dbe1d8' ),
	);

	foreach ( $colors as $id => $meta ) {
		$wp_customize->add_setting( $id, array( 'default' => $meta[1], 'sanitize_callback' => 'sanitize_hex_color' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => __( $meta[0], 'sparsha-wp' ), 'section' => 'sparsha_colors' ) ) );
	}

	// ═══════════════════════════════════════
	// 4. TYPOGRAPHY
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_typography', array(
		'title' => __( 'Typography', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$font_choices = array(
		'Inter'       => 'Inter',
		'Poppins'     => 'Poppins',
		'Lato'        => 'Lato',
		'Roboto'      => 'Roboto',
		'Open Sans'   => 'Open Sans',
		'Montserrat'  => 'Montserrat',
		'Nunito'      => 'Nunito',
		'Raleway'     => 'Raleway',
	);

	$heading_font_choices = array(
		'Cinzel'      => 'Cinzel',
		'Playfair Display' => 'Playfair Display',
		'Cormorant Garamond' => 'Cormorant Garamond',
		'Lora'        => 'Lora',
		'Merriweather' => 'Merriweather',
	);

	$wp_customize->add_setting( 'body_font', array( 'default' => 'Inter', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'body_font', array( 'label' => __( 'Body Font', 'sparsha-wp' ), 'section' => 'sparsha_typography', 'type' => 'select', 'choices' => $font_choices ) );

	$wp_customize->add_setting( 'heading_font', array( 'default' => 'Cormorant Garamond', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'heading_font', array( 'label' => __( 'Heading Font', 'sparsha-wp' ), 'section' => 'sparsha_typography', 'type' => 'select', 'choices' => $heading_font_choices ) );

	$wp_customize->add_setting( 'base_font_size', array( 'default' => 16, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'base_font_size', array( 'label' => __( 'Base Font Size (px)', 'sparsha-wp' ), 'section' => 'sparsha_typography', 'type' => 'number', 'input_attrs' => array( 'min' => 12, 'max' => 24 ) ) );

	$wp_customize->add_setting( 'base_line_height', array( 'default' => '1.6', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'base_line_height', array( 'label' => __( 'Line Height', 'sparsha-wp' ), 'section' => 'sparsha_typography', 'type' => 'text' ) );

	// ═══════════════════════════════════════
	// 5. HEADER SETTINGS
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_header', array(
		'title' => __( 'Header Settings', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$wp_customize->add_setting( 'sparsha_sticky_header', array( 'default' => true, 'sanitize_callback' => $bool_sanitize ) );
	$wp_customize->add_control( 'sparsha_sticky_header', array( 'label' => __( 'Enable Sticky Header', 'sparsha-wp' ), 'section' => 'sparsha_header', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'sparsha_transparent_header', array( 'default' => true, 'sanitize_callback' => $bool_sanitize ) );
	$wp_customize->add_control( 'sparsha_transparent_header', array( 'label' => __( 'Enable Transparent Header', 'sparsha-wp' ), 'section' => 'sparsha_header', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'sparsha_header_cta_enable', array( 'default' => true, 'sanitize_callback' => $bool_sanitize ) );
	$wp_customize->add_control( 'sparsha_header_cta_enable', array( 'label' => __( 'Enable Header CTA Button', 'sparsha-wp' ), 'section' => 'sparsha_header', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'sparsha_header_cta_text', array( 'default' => 'Book Appointment', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_header_cta_text', array( 'label' => __( 'CTA Button Text', 'sparsha-wp' ), 'section' => 'sparsha_header', 'type' => 'text' ) );

	$wp_customize->add_setting( 'sparsha_header_cta_link', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'sparsha_header_cta_link', array( 'label' => __( 'CTA Button Link', 'sparsha-wp' ), 'description' => __( 'Leave blank to link to contact page.', 'sparsha-wp' ), 'section' => 'sparsha_header', 'type' => 'url' ) );

	// ═══════════════════════════════════════
	// 6. CONTACT INFO
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_contact', array(
		'title' => __( 'Contact Information', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$contact_fields = array(
		'sparsha_phone'         => array( 'Phone Number', '+40 737 780 780' ),
		'sparsha_phone_href'    => array( 'Phone href', '+40737780780' ),
		'sparsha_phone_2'       => array( 'Phone Number 2', '' ),
		'sparsha_phone_2_href'  => array( 'Phone 2 href', '' ),
		'sparsha_whatsapp'      => array( 'WhatsApp Number (display)', '+40 737 780 780' ),
		'sparsha_whatsapp_href' => array( 'WhatsApp Number (digits only, no +)', '40737780780' ),
		'sparsha_email'         => array( 'Email Address', '' ),
		'sparsha_address'       => array( 'Address', 'Bd. Pipera nr. 1-VIII D, Voluntari, Romania' ),
		'sparsha_address_short' => array( 'Address Short', 'Bd. Pipera nr. 1-VIII D, Voluntari' ),
	);

	foreach ( $contact_fields as $id => $meta ) {
		if ( ! $wp_customize->get_setting( $id ) ) {
			$type     = ( $id === 'sparsha_address' ) ? 'textarea' : 'text';
			$sanitize = ( $id === 'sparsha_address' ) ? 'sanitize_textarea_field' : 'sanitize_text_field';
			$wp_customize->add_setting( $id, array( 'default' => $meta[1], 'sanitize_callback' => $sanitize ) );
			$wp_customize->add_control( $id, array( 'label' => $meta[0], 'section' => 'sparsha_contact', 'type' => $type ) );
		}
	}

	// ═══════════════════════════════════════
	// 7. BUSINESS HOURS
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_hours', array(
		'title' => __( 'Business Hours', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$hours_fields = array(
		'sparsha_hours_weekday' => array( 'Mon – Fri', '10:00 – 20:00' ),
		'sparsha_hours_sat'     => array( 'Saturday', '10:00 – 18:00' ),
		'sparsha_hours_sun'     => array( 'Sunday', 'By appointment' ),
	);

	foreach ( $hours_fields as $id => $meta ) {
		if ( ! $wp_customize->get_setting( $id ) ) {
			$wp_customize->add_setting( $id, array( 'default' => $meta[1], 'sanitize_callback' => 'sanitize_text_field' ) );
			$wp_customize->add_control( $id, array( 'label' => $meta[0], 'section' => 'sparsha_hours', 'type' => 'text' ) );
		}
	}

	// ═══════════════════════════════════════
	// 8. SOCIAL LINKS
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_social', array(
		'title' => __( 'Social Links', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$social_fields = array(
		'sparsha_facebook'  => array( 'Facebook URL', '' ),
		'sparsha_instagram' => array( 'Instagram URL', '' ),
		'sparsha_youtube'   => array( 'YouTube URL', 'https://www.youtube.com/channel/UC_hTPa9a8_gnHSOKD1JMh5g' ),
		'sparsha_tiktok'    => array( 'TikTok URL', '#' ),
	);

	foreach ( $social_fields as $id => $meta ) {
		if ( ! $wp_customize->get_setting( $id ) ) {
			$wp_customize->add_setting( $id, array( 'default' => $meta[1], 'sanitize_callback' => 'esc_url_raw' ) );
			$wp_customize->add_control( $id, array( 'label' => $meta[0], 'section' => 'sparsha_social', 'type' => 'url' ) );
		}
	}

	// ═══════════════════════════════════════
	// 9. PAYMENT CARDS (WE ACCEPT)
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_payment_cards', array(
		'title' => __( 'Payment Cards (We Accept)', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	for ( $i = 1; $i <= 5; $i++ ) {
		$wp_customize->add_setting( 'sparsha_payment_card_' . $i, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'sparsha_payment_card_' . $i, array(
			'label'    => sprintf( __( 'Payment Card %d Image', 'sparsha-wp' ), $i ),
			'section'  => 'sparsha_payment_cards',
			'settings' => 'sparsha_payment_card_' . $i,
		) ) );
	}

	// ═══════════════════════════════════════
	// 9. FOOTER SETTINGS
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_footer_settings', array(
		'title' => __( 'Footer Settings', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$wp_customize->add_setting( 'sparsha_copyright', array(
		'default'           => '© ' . date( 'Y' ) . ' Sparsha Ayurveda Centre · Budapest, Hungary. All rights reserved.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'sparsha_copyright', array(
		'label'   => __( 'Copyright Text', 'sparsha-wp' ),
		'section' => 'sparsha_footer_settings',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'sparsha_footer_layout', array( 'default' => '4-columns', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_footer_layout', array(
		'label'   => __( 'Footer Layout', 'sparsha-wp' ),
		'section' => 'sparsha_footer_settings',
		'type'    => 'select',
		'choices' => array(
			'4-columns' => __( '4 Columns', 'sparsha-wp' ),
			'3-columns' => __( '3 Columns', 'sparsha-wp' ),
			'2-columns' => __( '2 Columns', 'sparsha-wp' ),
			'1-column'  => __( '1 Column', 'sparsha-wp' ),
		),
	) );

	// ═══════════════════════════════════════
	// 9b. FOOTER COLUMNS
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_footer_columns', array(
		'title' => __( 'Footer Columns', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	// ── Brand column ──
	$wp_customize->add_setting( 'sparsha_footer_brand_desc', array(
		'default'           => 'Authentic Ayurvedic healing rooted in the traditions of Kerala, India — bringing natural wellness to the heart of Europe since 2012.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'sparsha_footer_brand_desc', array(
		'label'   => __( 'Brand Description', 'sparsha-wp' ),
		'description' => __( 'Short paragraph shown under the footer logo.', 'sparsha-wp' ),
		'section' => 'sparsha_footer_columns',
		'type'    => 'textarea',
	) );

	// ── Services column ──
	$wp_customize->add_setting( 'sparsha_footer_services_title', array(
		'default'           => 'Services',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'sparsha_footer_services_title', array(
		'label'   => __( 'Services Column — Title', 'sparsha-wp' ),
		'section' => 'sparsha_footer_columns',
		'type'    => 'text',
	) );

	// ── Explore column ──
	$wp_customize->add_setting( 'sparsha_footer_explore_title', array(
		'default'           => 'Explore',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'sparsha_footer_explore_title', array(
		'label'   => __( 'Explore Column — Title', 'sparsha-wp' ),
		'section' => 'sparsha_footer_columns',
		'type'    => 'text',
	) );

	// ── Contact column heading ──
	$wp_customize->add_setting( 'sparsha_footer_contact_title', array(
		'default'           => 'Contact Us',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'sparsha_footer_contact_title', array(
		'label'   => __( 'Contact Column — Title', 'sparsha-wp' ),
		'section' => 'sparsha_footer_columns',
		'type'    => 'text',
	) );

	// ═══════════════════════════════════════
	// 9d. TREATMENT SIDEBAR LABELS
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_treatment_sidebar', array(
		'title' => __( 'Treatment Sidebar Labels', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$tr_labels = array(
		'sparsha_tr_heading'        => array( 'Sidebar Heading', 'Treatment Details' ),
		'sparsha_tr_duration_label' => array( 'Duration Label', 'Duration' ),
		'sparsha_tr_price_label'    => array( 'Price Label', 'Price' ),
		'sparsha_tr_pricing_label'  => array( 'Pricing Options Heading', 'Pricing Options' ),
		'sparsha_tr_book_text'      => array( 'Book Button Text', 'Book This Treatment' ),
		'sparsha_tr_call_text'      => array( 'Call Button Prefix', 'Call' ),
		'sparsha_tr_back_text'      => array( 'Back Link Text', 'All Treatments' ),
		'sparsha_tr_gallery_label'  => array( 'Gallery Heading', 'Treatment Gallery' ),
	);
	foreach ( $tr_labels as $id => $meta ) {
		$wp_customize->add_setting( $id, array( 'default' => $meta[1], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $id, array( 'label' => __( $meta[0], 'sparsha-wp' ), 'section' => 'sparsha_treatment_sidebar', 'type' => 'text' ) );
	}

	// ═══════════════════════════════════════
	// 9f. UI LABELS (site-wide microcopy)
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_ui_labels', array(
		'title' => __( 'UI Labels', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$ui_labels = array(
		// Buttons / links
		'label_read_more'          => array( 'Read More', 'Read More' ),
		'label_know_more'          => array( 'Know More', 'KNOW MORE' ),
		'label_view_all'           => array( 'View All', 'View All' ),
		'label_book_now'           => array( 'Book Now', 'Book Now' ),
		'label_book_appointment'   => array( 'Book Appointment', 'Book Appointment' ),
		'label_send_request'       => array( 'Send Request', 'Send Appointment Request' ),
		'label_view_details'       => array( 'View Full Details', 'View Full Details' ),
		'label_menu'               => array( 'Mobile Menu', 'Menu' ),
		// Section titles
		'label_follow_us'          => array( 'Follow Us', 'Follow Us' ),
		'label_visit_us'           => array( 'Visit Us', 'Visit Us' ),
		'label_call_us'            => array( 'Call Us', 'Call Us' ),
		'label_email_us'           => array( 'Email Us', 'Email Us' ),
		'label_opening_hours'      => array( 'Opening Hours', 'Opening Hours' ),
		'label_hours_weekday'      => array( 'Mon – Fri', 'Mon – Fri' ),
		'label_hours_sat'          => array( 'Saturday', 'Saturday' ),
		'label_hours_sun'          => array( 'Sunday', 'Sunday' ),
		'label_recent_posts'       => array( 'Recent Posts', 'Recent Posts' ),
		'label_related_posts'      => array( 'Related Posts', 'Related Posts' ),
		'label_share'              => array( 'Share', 'Share' ),
		'label_previous'           => array( 'Previous', 'Previous' ),
		'label_next'               => array( 'Next', 'Next' ),
		'label_search'             => array( 'Search', 'Search' ),
		'label_all_categories'     => array( 'All Categories', 'All' ),
		'label_by_author'          => array( 'By Author', 'By' ),
		'label_no_posts'           => array( 'No Posts Text', 'No posts found.' ),
	);
	foreach ( $ui_labels as $id => $meta ) {
		$wp_customize->add_setting( $id, array( 'default' => $meta[1], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $id, array( 'label' => $meta[0], 'section' => 'sparsha_ui_labels', 'type' => 'text' ) );
	}

	// ═══════════════════════════════════════
	// 9e. PRE-FOOTER CTA
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_cta', array(
		'title' => __( 'Pre-Footer CTA', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$wp_customize->add_setting( 'sparsha_cta_enable', array( 'default' => true, 'sanitize_callback' => $bool_sanitize ) );
	$wp_customize->add_control( 'sparsha_cta_enable', array( 'label' => __( 'Enable Pre-Footer CTA', 'sparsha-wp' ), 'section' => 'sparsha_cta', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'sparsha_cta_bg', array( 'default' => '', 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'sparsha_cta_bg', array( 'label' => __( 'Background Image', 'sparsha-wp' ), 'section' => 'sparsha_cta', 'mime_type' => 'image' ) ) );

	$wp_customize->add_setting( 'sparsha_cta_heading', array( 'default' => 'Ready to Begin Your Healing Journey?', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_cta_heading', array( 'label' => __( 'Heading', 'sparsha-wp' ), 'section' => 'sparsha_cta', 'type' => 'text' ) );

	$wp_customize->add_setting( 'sparsha_cta_sub', array( 'default' => 'Our specialists will guide you — no prior knowledge of Ayurveda needed.', 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'sparsha_cta_sub', array( 'label' => __( 'Subheading', 'sparsha-wp' ), 'section' => 'sparsha_cta', 'type' => 'textarea' ) );

	$wp_customize->add_setting( 'sparsha_cta_p_text', array( 'default' => 'Book Appointment', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_cta_p_text', array( 'label' => __( 'Primary Button Text', 'sparsha-wp' ), 'section' => 'sparsha_cta', 'type' => 'text' ) );
	$wp_customize->add_setting( 'sparsha_cta_p_link', array( 'default' => '/contact', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_cta_p_link', array( 'label' => __( 'Primary Button Link', 'sparsha-wp' ), 'section' => 'sparsha_cta', 'type' => 'text' ) );

	$wp_customize->add_setting( 'sparsha_cta_s_text', array( 'default' => 'Gift Voucher', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_cta_s_text', array( 'label' => __( 'Secondary Button Text', 'sparsha-wp' ), 'section' => 'sparsha_cta', 'type' => 'text' ) );
	$wp_customize->add_setting( 'sparsha_cta_s_link', array( 'default' => '/gift-vouchers', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_cta_s_link', array( 'label' => __( 'Secondary Button Link', 'sparsha-wp' ), 'section' => 'sparsha_cta', 'type' => 'text' ) );

	// ═══════════════════════════════════════
	// 9c. FLOATING CONTACT BUTTONS
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_floating', array(
		'title' => __( 'Floating Contact Buttons', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$wp_customize->add_setting( 'sparsha_float_enable', array( 'default' => true, 'sanitize_callback' => $bool_sanitize ) );
	$wp_customize->add_control( 'sparsha_float_enable', array( 'label' => __( 'Enable Floating Buttons', 'sparsha-wp' ), 'section' => 'sparsha_floating', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'sparsha_float_phone', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_float_phone', array( 'label' => __( 'Phone Number (with country code, no spaces)', 'sparsha-wp' ), 'description' => __( 'Leave blank to use Contact Info → Phone href.', 'sparsha-wp' ), 'section' => 'sparsha_floating', 'type' => 'text' ) );

	$wp_customize->add_setting( 'sparsha_float_whatsapp', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_float_whatsapp', array( 'label' => __( 'WhatsApp Number (digits only, no + or spaces)', 'sparsha-wp' ), 'description' => __( 'Example: 36705625113. Leave blank to hide WhatsApp button.', 'sparsha-wp' ), 'section' => 'sparsha_floating', 'type' => 'text' ) );

	$wp_customize->add_setting( 'sparsha_float_whatsapp_msg', array( 'default' => 'Hello, I would like to know more about your Ayurveda treatments.', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_float_whatsapp_msg', array( 'label' => __( 'WhatsApp Prefill Message', 'sparsha-wp' ), 'section' => 'sparsha_floating', 'type' => 'text' ) );

	$wp_customize->add_setting( 'sparsha_float_position', array( 'default' => 'right', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_float_position', array( 'label' => __( 'Position', 'sparsha-wp' ), 'section' => 'sparsha_floating', 'type' => 'select', 'choices' => array( 'right' => 'Bottom Right', 'left' => 'Bottom Left' ) ) );

	// ═══════════════════════════════════════
	// 10. YOUTUBE EMBED
	// ═══════════════════════════════════════
	$wp_customize->add_section( 'sparsha_youtube_section', array(
		'title' => __( 'YouTube Embed', 'sparsha-wp' ),
		'panel' => 'sparsha_panel',
	) );

	$wp_customize->add_setting( 'sparsha_youtube_embed', array( 'default' => 'MpjKyJEzNUQ', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'sparsha_youtube_embed', array(
		'label'       => __( 'YouTube Video ID', 'sparsha-wp' ),
		'description' => __( 'Enter only the video ID (e.g. MpjKyJEzNUQ)', 'sparsha-wp' ),
		'section'     => 'sparsha_youtube_section',
		'type'        => 'text',
	) );
}

// ═══════════════════════════════════════
// CSS VARIABLE INJECTION
// ═══════════════════════════════════════
add_action( 'wp_enqueue_scripts', 'sparsha_customizer_css', 20 );
function sparsha_customizer_css() {
	$primary   = get_theme_mod( 'primary_color',      '#285a14' );
	$secondary = get_theme_mod( 'secondary_color',    '#347319' );
	$accent    = get_theme_mod( 'accent_color',       '#f59e0b' );
	$text      = get_theme_mod( 'text_color',         '#2c1810' );
	$bg        = get_theme_mod( 'bg_color',           '#fdf8f0' );
	$heading   = get_theme_mod( 'heading_color',      '#1a3a10' );
	$border    = get_theme_mod( 'border_color',       '#ddefd3' );
	$hdr_bg    = get_theme_mod( 'header_bg_color',    '#ffffff' );
	$hdr_txt   = get_theme_mod( 'header_text_color',  '#1a3a10' );
	$ftr_bg    = get_theme_mod( 'footer_bg_color',    '#1a3a10' );
	$ftr_txt   = get_theme_mod( 'footer_text_color',  '#dbe1d8' );
	$tb_bg     = get_theme_mod( 'top_bar_bg_color',   '#1a3a10' );
	$tb_txt    = get_theme_mod( 'top_bar_text_color', '#fdf8f0' );

	$body_font    = get_theme_mod( 'body_font', 'Inter' );
	$heading_font = get_theme_mod( 'heading_font', 'Cormorant Garamond' );
	$font_size    = absint( get_theme_mod( 'base_font_size', 16 ) );
	$line_height  = esc_attr( get_theme_mod( 'base_line_height', '1.6' ) );

	$logo_d  = absint( get_theme_mod( 'header_logo_width_desktop', 160 ) );
	$logo_m  = absint( get_theme_mod( 'header_logo_width_mobile', 120 ) );
	$flogo_d = absint( get_theme_mod( 'footer_logo_width_desktop', 140 ) );
	$flogo_m = absint( get_theme_mod( 'footer_logo_width_mobile', 100 ) );

	// Derived alpha colors for borders, overlays, etc.
	$primary_rgb = sparsha_hex_to_rgb( $primary );
	$accent_rgb  = sparsha_hex_to_rgb( $accent );
	$heading_rgb = sparsha_hex_to_rgb( $heading );
	$border_rgb  = sparsha_hex_to_rgb( $border );
	$ftr_bg_rgb  = sparsha_hex_to_rgb( $ftr_bg );
	$text_rgb    = sparsha_hex_to_rgb( $text );

	$css = ":root {
		--primary-color: {$primary};
		--secondary-color: {$secondary};
		--accent-color: {$accent};
		--text-color: {$text};
		--bg-color: {$bg};
		--heading-color: {$heading};
		--border-color: {$border};
		--header-bg-color: {$hdr_bg};
		--header-text-color: {$hdr_txt};
		--top-bar-bg-color: {$tb_bg};
		--top-bar-text-color: {$tb_txt};
		--footer-bg-color: {$ftr_bg};
		--footer-text-color: {$ftr_txt};
		--body-font: '{$body_font}', system-ui, sans-serif;
		--heading-font: '{$heading_font}', serif;
		--base-font-size: {$font_size}px;
		--base-line-height: {$line_height};
		--primary-rgb: {$primary_rgb};
		--accent-rgb: {$accent_rgb};
		--heading-rgb: {$heading_rgb};
		--border-rgb: {$border_rgb};
		--footer-bg-rgb: {$ftr_bg_rgb};
		--text-rgb: {$text_rgb};
	}

	/* ── Global body / heading colors driven by Customizer ── */
	body { color: var(--text-color); background-color: var(--bg-color); }
	h1, h2, h3, h4, h5, h6 { color: var(--heading-color); }

	/* ── Map every Tailwind utility class → Customizer variables ── */
	/* Backgrounds (forest = primary/heading scale) */
	.bg-cream { background-color: var(--bg-color) !important; }
	.bg-forest-50  { background-color: rgba(var(--border-rgb), 0.35) !important; }
	.bg-forest-100 { background-color: var(--border-color) !important; }
	.bg-forest-200 { background-color: rgba(var(--primary-rgb), 0.25) !important; }
	.bg-forest-300 { background-color: rgba(var(--primary-rgb), 0.40) !important; }
	.bg-forest-400 { background-color: rgba(var(--primary-rgb), 0.65) !important; }
	.bg-forest-500 { background-color: var(--secondary-color) !important; }
	.bg-forest-600 { background-color: var(--secondary-color) !important; }
	.bg-forest-700 { background-color: var(--primary-color) !important; }
	.bg-forest-800 { background-color: rgba(var(--primary-rgb), 0.92) !important; }
	.bg-forest-900,
	.bg-forest-950 { background-color: var(--heading-color) !important; }
	/* Amber (accent scale) */
	.bg-amber-50  { background-color: rgba(var(--accent-rgb), 0.10) !important; }
	.bg-amber-100 { background-color: rgba(var(--accent-rgb), 0.20) !important; }
	.bg-amber-200 { background-color: rgba(var(--accent-rgb), 0.35) !important; }
	.bg-amber-300 { background-color: rgba(var(--accent-rgb), 0.55) !important; }
	.bg-amber-400 { background-color: rgba(var(--accent-rgb), 0.85) !important; }
	.bg-amber-500 { background-color: var(--accent-color) !important; }
	.bg-amber-600 { background-color: rgba(var(--accent-rgb), 0.92) !important; }
	.bg-amber-700 { background-color: rgba(var(--accent-rgb), 0.80) !important; }
	.bg-bark { background-color: var(--text-color) !important; }

	/* Text colors — forest scale (body shades route to --text-color) */
	.text-forest-100 { color: rgba(var(--border-rgb), 0.95) !important; }
	.text-forest-200 { color: rgba(var(--text-rgb), 0.55) !important; }
	.text-forest-300 { color: rgba(var(--text-rgb), 0.65) !important; }
	.text-forest-400 { color: rgba(var(--text-rgb), 0.78) !important; }
	.text-forest-500 { color: rgba(var(--text-rgb), 0.88) !important; }  /* muted body text */
	.text-forest-600 { color: var(--text-color) !important; }            /* default body text */
	.text-forest-700 { color: var(--primary-color) !important; }         /* links / accents */
	.text-forest-800 { color: var(--heading-color) !important; }
	.text-forest-900 { color: var(--heading-color) !important; }
	p { color: var(--text-color); }
	/* Text colors — amber scale */
	.text-amber-100 { color: rgba(var(--accent-rgb), 0.40) !important; }
	.text-amber-200 { color: rgba(var(--accent-rgb), 0.65) !important; }
	.text-amber-300 { color: rgba(var(--accent-rgb), 0.85) !important; }
	.text-amber-400 { color: var(--accent-color) !important; }
	.text-amber-500 { color: var(--accent-color) !important; }
	.text-amber-600 { color: rgba(var(--accent-rgb), 0.92) !important; }
	.text-amber-700 { color: rgba(var(--accent-rgb), 0.82) !important; }
	.text-amber-800 { color: rgba(var(--accent-rgb), 0.70) !important; }
	.text-bark { color: var(--text-color) !important; }
	.text-cream { color: var(--bg-color) !important; }

	/* Borders */
	.border-forest-100 { border-color: var(--border-color) !important; }
	.border-forest-200 { border-color: rgba(var(--primary-rgb), 0.25) !important; }
	.border-forest-300 { border-color: rgba(var(--primary-rgb), 0.40) !important; }
	.border-forest-700 { border-color: var(--primary-color) !important; }
	.border-forest-800 { border-color: var(--heading-color) !important; }
	.border-forest-900 { border-color: var(--heading-color) !important; }
	.border-amber-400 { border-color: rgba(var(--accent-rgb), 0.85) !important; }
	.border-amber-500 { border-color: var(--accent-color) !important; }

	/* Hover states */
	.hover\\:bg-cream:hover { background-color: var(--bg-color) !important; }
	.hover\\:bg-forest-200:hover { background-color: rgba(var(--primary-rgb), 0.25) !important; }
	.hover\\:bg-forest-700:hover { background-color: var(--primary-color) !important; }
	.hover\\:bg-forest-800:hover { background-color: rgba(var(--primary-rgb), 0.92) !important; }
	.hover\\:bg-forest-900:hover { background-color: var(--heading-color) !important; }
	.hover\\:bg-amber-50:hover { background-color: rgba(var(--accent-rgb), 0.10) !important; }
	.hover\\:bg-amber-400:hover { background-color: rgba(var(--accent-rgb), 0.85) !important; }
	.hover\\:bg-amber-500:hover { background-color: var(--accent-color) !important; }
	.hover\\:text-amber-100:hover { color: rgba(var(--accent-rgb), 0.40) !important; }
	.hover\\:text-amber-300:hover { color: rgba(var(--accent-rgb), 0.85) !important; }
	.hover\\:text-amber-400:hover { color: var(--accent-color) !important; }
	.hover\\:text-amber-600:hover { color: rgba(var(--accent-rgb), 0.92) !important; }
	.hover\\:text-amber-700:hover { color: rgba(var(--accent-rgb), 0.82) !important; }
	.hover\\:text-amber-800:hover { color: rgba(var(--accent-rgb), 0.70) !important; }
	.hover\\:border-amber-400:hover { border-color: rgba(var(--accent-rgb), 0.85) !important; }
	.hover\\:border-amber-500:hover { border-color: var(--accent-color) !important; }
	.hover\\:border-forest-700:hover { border-color: var(--primary-color) !important; }
	.hover\\:border-forest-800:hover { border-color: var(--heading-color) !important; }
	.hover\\:border-forest-900:hover { border-color: var(--heading-color) !important; }

	/* group-hover variants */
	.group:hover .group-hover\\:bg-amber-50 { background-color: rgba(var(--accent-rgb), 0.10) !important; }
	.group:hover .group-hover\\:bg-amber-500 { background-color: var(--accent-color) !important; }
	.group:hover .group-hover\\:bg-forest-700 { background-color: var(--primary-color) !important; }
	.group:hover .group-hover\\:bg-forest-900 { background-color: var(--heading-color) !important; }
	.group:hover .group-hover\\:text-amber-300 { color: rgba(var(--accent-rgb), 0.85) !important; }
	.group:hover .group-hover\\:text-amber-700 { color: rgba(var(--accent-rgb), 0.82) !important; }

	/* Opacity-modified variants (re-derived from Customizer) */
	.bg-forest-900\\/70 { background-color: rgba(var(--heading-rgb), 0.7) !important; }
	.bg-forest-900\\/20 { background-color: rgba(var(--heading-rgb), 0.2) !important; }
	.bg-forest-900\\/10 { background-color: rgba(var(--heading-rgb), 0.1) !important; }
	.bg-amber-500\\/20 { background-color: rgba(var(--accent-rgb), 0.2) !important; }
	.from-forest-900\\/55 { --tw-gradient-from: rgba(var(--heading-rgb), 0.55) !important; }
	.from-forest-900\\/85 { --tw-gradient-from: rgba(var(--heading-rgb), 0.85) !important; }
	.from-forest-900\\/90 { --tw-gradient-from: rgba(var(--heading-rgb), 0.90) !important; }
	.via-forest-900\\/40 { --tw-gradient-via: rgba(var(--heading-rgb), 0.40) !important; }
	.via-forest-900\\/45 { --tw-gradient-via: rgba(var(--heading-rgb), 0.45) !important; }
	.via-forest-900\\/50 { --tw-gradient-via: rgba(var(--heading-rgb), 0.50) !important; }
	.to-forest-900\\/70  { --tw-gradient-to: rgba(var(--heading-rgb), 0.70) !important; }
	.to-forest-900\\/75  { --tw-gradient-to: rgba(var(--heading-rgb), 0.75) !important; }
	.to-forest-900\\/20  { --tw-gradient-to: rgba(var(--heading-rgb), 0.20) !important; }

	/* Header — solid scrolled state uses Customizer header colors */
	#nav.scrolled #nav-inner {
		background: var(--header-bg-color) !important;
	}
	#nav.scrolled .nav-brand,
	#nav.scrolled .nav-links a,
	#nav.scrolled .nav-phone,
	#nav.scrolled .nav-menu-btn { color: var(--header-text-color) !important; }

	/* Footer */
	footer.bg-forest-900,
	footer[class*='bg-forest'] { background-color: var(--footer-bg-color) !important; }
	footer { color: var(--footer-text-color) !important; }
	footer p, footer span, footer li, footer a:not(:hover) { color: var(--footer-text-color); }

	/* ── DARK SURFACES: always force light text, regardless of Customizer Text/Heading color ── */
	.bg-forest-700, .bg-forest-700 *,
	.bg-forest-800, .bg-forest-800 *,
	.bg-forest-900, .bg-forest-900 *,
	.bg-forest-950, .bg-forest-950 *,
	.bg-bark, .bg-bark *,
	footer, footer *,
	.sparsha-top-bar, .sparsha-top-bar * { --on-dark: 1; }

	/* Body / paragraph shades become light on dark BGs */
	.bg-forest-700 .text-forest-100, .bg-forest-700 .text-forest-200, .bg-forest-700 .text-forest-300, .bg-forest-700 .text-forest-400, .bg-forest-700 .text-forest-500, .bg-forest-700 .text-forest-600, .bg-forest-700 .text-forest-700, .bg-forest-700 .text-forest-800,
	.bg-forest-800 .text-forest-100, .bg-forest-800 .text-forest-200, .bg-forest-800 .text-forest-300, .bg-forest-800 .text-forest-400, .bg-forest-800 .text-forest-500, .bg-forest-800 .text-forest-600, .bg-forest-800 .text-forest-700, .bg-forest-800 .text-forest-800,
	.bg-forest-900 .text-forest-100, .bg-forest-900 .text-forest-200, .bg-forest-900 .text-forest-300, .bg-forest-900 .text-forest-400, .bg-forest-900 .text-forest-500, .bg-forest-900 .text-forest-600, .bg-forest-900 .text-forest-700, .bg-forest-900 .text-forest-800,
	.bg-forest-950 .text-forest-100, .bg-forest-950 .text-forest-200, .bg-forest-950 .text-forest-300, .bg-forest-950 .text-forest-400, .bg-forest-950 .text-forest-500, .bg-forest-950 .text-forest-600, .bg-forest-950 .text-forest-700, .bg-forest-950 .text-forest-800,
	.bg-bark .text-forest-100, .bg-bark .text-forest-200, .bg-bark .text-forest-300, .bg-bark .text-forest-400, .bg-bark .text-forest-500, .bg-bark .text-forest-600, .bg-bark .text-forest-700, .bg-bark .text-forest-800,
	footer .text-forest-100, footer .text-forest-200, footer .text-forest-300, footer .text-forest-400, footer .text-forest-500, footer .text-forest-600, footer .text-forest-700, footer .text-forest-800,
	.sparsha-top-bar .text-forest-100, .sparsha-top-bar .text-forest-200, .sparsha-top-bar .text-forest-300, .sparsha-top-bar .text-forest-400, .sparsha-top-bar .text-forest-500, .sparsha-top-bar .text-forest-600, .sparsha-top-bar .text-forest-700 {
		color: rgba(255, 255, 255, 0.85) !important;
	}

	/* Headings inside dark surfaces */
	.bg-forest-700 h1, .bg-forest-700 h2, .bg-forest-700 h3, .bg-forest-700 h4, .bg-forest-700 h5, .bg-forest-700 h6,
	.bg-forest-800 h1, .bg-forest-800 h2, .bg-forest-800 h3, .bg-forest-800 h4, .bg-forest-800 h5, .bg-forest-800 h6,
	.bg-forest-900 h1, .bg-forest-900 h2, .bg-forest-900 h3, .bg-forest-900 h4, .bg-forest-900 h5, .bg-forest-900 h6,
	.bg-forest-950 h1, .bg-forest-950 h2, .bg-forest-950 h3, .bg-forest-950 h4, .bg-forest-950 h5, .bg-forest-950 h6,
	.bg-bark h1, .bg-bark h2, .bg-bark h3, .bg-bark h4, .bg-bark h5, .bg-bark h6,
	footer h1, footer h2, footer h3, footer h4, footer h5, footer h6 {
		color: #ffffff !important;
	}

	/* Paragraphs / list items / spans / generic body text inside dark surfaces */
	.bg-forest-700 p, .bg-forest-800 p, .bg-forest-900 p, .bg-forest-950 p, .bg-bark p, footer p,
	.bg-forest-700 li, .bg-forest-800 li, .bg-forest-900 li, .bg-forest-950 li, .bg-bark li, footer li,
	.bg-forest-700 span, .bg-forest-800 span, .bg-forest-900 span, .bg-forest-950 span, .bg-bark span {
		color: rgba(255, 255, 255, 0.92) !important;
	}

	/* Preserve text colors inside light/white cards or review widgets placed on dark surfaces */
	.bg-forest-700 .bg-white, .bg-forest-800 .bg-white, .bg-forest-900 .bg-white, .bg-forest-950 .bg-white, .bg-bark .bg-white,
	.bg-forest-700 .bg-white *, .bg-forest-800 .bg-white *, .bg-forest-900 .bg-white *, .bg-forest-950 .bg-white *, .bg-bark .bg-white *,
	.ti-widget, .ti-widget *, .ti-review-item, .ti-review-item * {
		color: inherit;
	}
	.ti-widget .ti-review-text-container, .ti-widget .ti-name, .ti-widget .ti-date, .ti-widget .ti-review-content, .ti-review-item .ti-review-text-container {
		color: #333333 !important;
	}

	/* But preserve explicit accent / white / black text inside dark surfaces */
	.bg-forest-700 [class*='text-amber'], .bg-forest-800 [class*='text-amber'], .bg-forest-900 [class*='text-amber'], .bg-forest-950 [class*='text-amber'], .bg-bark [class*='text-amber'], footer [class*='text-amber'] { color: var(--accent-color) !important; }
	.bg-forest-700 .text-white, .bg-forest-800 .text-white, .bg-forest-900 .text-white, .bg-forest-950 .text-white, .bg-bark .text-white, footer .text-white { color: #ffffff !important; }
	.bg-forest-700 .text-bark, .bg-forest-800 .text-bark, .bg-forest-900 .text-bark, .bg-forest-950 .text-bark, .bg-bark .text-bark, footer .text-bark { color: var(--text-color) !important; }
	.bg-forest-700 .text-amber-300, .bg-forest-800 .text-amber-300, .bg-forest-900 .text-amber-300, .bg-forest-950 .text-amber-300, footer .text-amber-300 { color: rgba(var(--accent-rgb), 0.95) !important; }
	.bg-forest-700 .text-amber-400, .bg-forest-800 .text-amber-400, .bg-forest-900 .text-amber-400, .bg-forest-950 .text-amber-400, footer .text-amber-400 { color: var(--accent-color) !important; }

	/* Default link colour inside dark surfaces */
	.bg-forest-700 a:not([class*='text-amber']):not(.text-white),
	.bg-forest-800 a:not([class*='text-amber']):not(.text-white),
	.bg-forest-900 a:not([class*='text-amber']):not(.text-white),
	.bg-forest-950 a:not([class*='text-amber']):not(.text-white),
	footer a:not([class*='text-amber']):not(.text-white) {
		color: rgba(255, 255, 255, 0.92) !important;
	}

	/* strong / em inside dark surfaces stay bright white */
	.bg-forest-700 strong, .bg-forest-800 strong, .bg-forest-900 strong, .bg-forest-950 strong, .bg-bark strong, footer strong { color: #ffffff !important; font-weight: 600; }

	/* Phone-booking box (used in contact page) — explicit override since rich content can contain bare elements */
	.cf7-phone-box, .cf7-phone-box p, .cf7-phone-box span, .cf7-phone-box div, .cf7-phone-box li { color: rgba(255, 255, 255, 0.92) !important; }
	.cf7-phone-box strong { color: #ffffff !important; }
	.cf7-phone-box a { color: var(--accent-color) !important; font-weight: 600; }
	.cf7-phone-box a:hover { color: rgba(var(--accent-rgb), 0.85) !important; }

	/* Green (forest) buttons — force cream text */
	a.bg-forest-500, a.bg-forest-600, a.bg-forest-700, a.bg-forest-800, a.bg-forest-900, a.bg-forest-950,
	button.bg-forest-500, button.bg-forest-600, button.bg-forest-700, button.bg-forest-800, button.bg-forest-900, button.bg-forest-950,
	.btn.bg-forest-500, .btn.bg-forest-600, .btn.bg-forest-700, .btn.bg-forest-800, .btn.bg-forest-900, .btn.bg-forest-950 {
		color: #f0dfb2 !important;
	}
	a.bg-forest-500 *, a.bg-forest-600 *, a.bg-forest-700 *, a.bg-forest-800 *, a.bg-forest-900 *, a.bg-forest-950 *,
	button.bg-forest-500 *, button.bg-forest-600 *, button.bg-forest-700 *, button.bg-forest-800 *, button.bg-forest-900 *, button.bg-forest-950 * {
		color: inherit !important;
	}

	/* Footer nav menus (Services/Explore column) — link style */
	.sparsha-footer-menu a { color: inherit; transition: color .2s ease; }
	.sparsha-footer-menu a:hover { color: var(--accent-color) !important; }
	.sparsha-footer-menu .sub-menu { display: none; }

	/* Footer social icons — force black icon on white bg (svg + fa <i>) */
	footer a.bg-white,
	footer a.bg-white i,
	footer a.bg-white svg { color: #000 !important; }
	footer a.bg-white:hover,
	footer a.bg-white:hover i,
	footer a.bg-white:hover svg { color: #fff !important; }

	/* Floating contact buttons */
	@keyframes wa-pulse {
		0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7), 0 6px 18px rgba(0,0,0,0.25); }
		70% { box-shadow: 0 0 0 14px rgba(37, 211, 102, 0), 0 6px 18px rgba(0,0,0,0.25); }
		100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0), 0 6px 18px rgba(0,0,0,0.25); }
	}
	@keyframes wa-wiggle {
		0%, 80%, 100% { transform: rotate(0deg) scale(1); }
		83% { transform: rotate(-10deg) scale(1.08); }
		87% { transform: rotate(10deg) scale(1.08); }
		91% { transform: rotate(-6deg) scale(1.08); }
		95% { transform: rotate(6deg) scale(1.08); }
	}
	.sparsha-float { position: fixed; bottom: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; }
	.sparsha-float--right { right: 20px; }
	.sparsha-float--left { left: 20px; }
	.sparsha-float__btn { width: 54px; height: 54px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff !important; box-shadow: 0 6px 18px rgba(0,0,0,0.25); transition: transform .2s ease, box-shadow .2s ease; }
	.sparsha-float__btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,0.3); }
	.sparsha-float__btn svg, .sparsha-float__btn i { display: flex; align-items: center; justify-content: center; line-height: 1; }
	.sparsha-float__btn--wa svg, .sparsha-float__btn--wa i { width: 32px; height: 32px; font-size: 30px; }
	.sparsha-float__btn--phone svg, .sparsha-float__btn--phone i { width: 24px; height: 24px; font-size: 22px; }
	.sparsha-float__btn--wa { background: #25D366; animation: wa-pulse 2s infinite, wa-wiggle 4.5s infinite; }
	.sparsha-float__btn--wa:hover { animation: none; }
	.sparsha-float__btn--phone { background: var(--primary-color, #285a14); }
	@media (max-width: 480px) { .sparsha-float { bottom: 14px; } .sparsha-float--right { right: 14px; } .sparsha-float--left { left: 14px; } .sparsha-float__btn { width: 50px; height: 50px; } .sparsha-float__btn--wa svg, .sparsha-float__btn--wa i { width: 28px; height: 28px; font-size: 26px; } .sparsha-float__btn--phone svg, .sparsha-float__btn--phone i { width: 22px; height: 22px; font-size: 20px; } }

	/* Top bar */
	.sparsha-top-bar { background: var(--top-bar-bg-color) !important; color: var(--top-bar-text-color) !important; }

	/* Logo widths */
	.custom-logo-link img, .site-logo img, .nav-logo {
		width: {$logo_d}px; height: auto;
	}
	@media (max-width: 767px) {
		.custom-logo-link img, .site-logo img, .nav-logo {
			width: {$logo_m}px; height: auto;
		}
	}
	.footer-logo img, footer .custom-logo-link img {
		width: {$flogo_d}px; height: auto;
	}
	@media (max-width: 767px) {
		.footer-logo img, footer .custom-logo-link img {
			width: {$flogo_m}px; height: auto;
		}
	}";

	wp_add_inline_style( 'sparsha-theme', $css );
}

/**
 * Convert a hex color (#abcdef) to comma-separated RGB string (171, 205, 239).
 */
function sparsha_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( strlen( $hex ) !== 6 ) {
		return '40, 90, 20';
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	return "{$r}, {$g}, {$b}";
}
