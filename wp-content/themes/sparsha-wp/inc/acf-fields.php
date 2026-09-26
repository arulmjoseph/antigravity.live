<?php
/**
 * Register ACF Field Groups programmatically.
 * These will also be saved to acf-json/ when edited in the admin.
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

// ═══════════════════════════════════════
// HOME PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_home_page',
	'title'    => 'Home Page Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-home.php' ) ) ),
	'menu_order' => 0,
	'fields'   => array(

		// ── Hero Tab ──
		array( 'key' => 'field_home_tab_hero', 'label' => 'Hero Slider', 'type' => 'tab' ),
		array( 'key' => 'field_home_hero_enable', 'label' => 'Enable Hero', 'name' => 'hero_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array(
			'key' => 'field_home_hero_slides', 'label' => 'Hero Slides', 'name' => 'hero_slides', 'type' => 'repeater',
			'min' => 1, 'max' => 6, 'layout' => 'block',
			'sub_fields' => array(
				array( 'key' => 'field_hero_slide_image', 'label' => 'Background Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Recommended: 1920×1080px' ),
				array( 'key' => 'field_hero_slide_label', 'label' => 'Label Text', 'name' => 'label', 'type' => 'text', 'default_value' => 'Authentic Ayurveda · Budapest, Hungary' ),
				array( 'key' => 'field_hero_slide_heading', 'label' => 'Heading', 'name' => 'heading', 'type' => 'text', 'instructions' => 'Main large heading' ),
				array( 'key' => 'field_hero_slide_highlight', 'label' => 'Heading Highlight', 'name' => 'highlight', 'type' => 'text', 'instructions' => 'Second line of text' ),
				array( 'key' => 'field_hero_slide_highlight_color', 'label' => 'Highlight Text Color', 'name' => 'highlight_color', 'type' => 'color_picker', 'default_value' => '#fcd34d', 'instructions' => 'Custom color for highlighted text (default: #fcd34d)' ),
				array( 'key' => 'field_hero_slide_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
				array( 'key' => 'field_hero_slide_cta1', 'label' => 'Primary CTA', 'name' => 'cta_primary', 'type' => 'link' ),
				array( 'key' => 'field_hero_slide_cta2', 'label' => 'Secondary CTA', 'name' => 'cta_secondary', 'type' => 'link' ),
			),
		),

		// ── About Tab ──
		array( 'key' => 'field_home_tab_about', 'label' => 'About Section', 'type' => 'tab' ),
		array( 'key' => 'field_home_about_enable', 'label' => 'Enable About', 'name' => 'about_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_home_about_label', 'label' => 'Label', 'name' => 'about_label', 'type' => 'text', 'default_value' => 'About Sparsha' ),
		array( 'key' => 'field_home_about_heading', 'label' => 'Heading', 'name' => 'about_heading', 'type' => 'text' ),
		array( 'key' => 'field_home_about_content', 'label' => 'Content', 'name' => 'about_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
		array( 'key' => 'field_home_about_image', 'label' => 'Image', 'name' => 'about_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_home_about_badge_num', 'label' => 'Badge Number', 'name' => 'about_badge_number', 'type' => 'text', 'default_value' => '12+' ),
		array( 'key' => 'field_home_about_badge_text', 'label' => 'Badge Text', 'name' => 'about_badge_text', 'type' => 'text', 'default_value' => 'Years in Europe' ),
		array( 'key' => 'field_home_about_origin_tag', 'label' => 'Origin Tag', 'name' => 'about_origin_tag', 'type' => 'text', 'default_value' => 'Rooted in Kerala, India' ),
		array( 'key' => 'field_home_about_cta', 'label' => 'CTA Button', 'name' => 'about_cta', 'type' => 'link' ),

		// ── Services Tab (section labels only — cards pulled from Treatments) ──
		array( 'key' => 'field_home_tab_services', 'label' => 'Services Section', 'type' => 'tab' ),
		array( 'key' => 'field_home_services_enable', 'label' => 'Enable Services', 'name' => 'services_enable', 'type' => 'true_false', 'default_value' => 1, 'instructions' => 'Section auto-pulls parent Treatments (CPT).' ),
		array( 'key' => 'field_home_services_label', 'label' => 'Kicker Label', 'name' => 'services_label', 'type' => 'text', 'default_value' => 'Our Treatments' ),
		array( 'key' => 'field_home_services_heading', 'label' => 'Heading', 'name' => 'services_heading', 'type' => 'text', 'default_value' => 'Experience the Bliss of Ayurveda With Us!' ),
		array( 'key' => 'field_home_services_desc', 'label' => 'Description', 'name' => 'services_description', 'type' => 'textarea', 'rows' => 2, 'default_value' => 'Revitalizing the mind, body, and spirit with authentic Ayurveda — each treatment tailored to your unique constitution (Prakriti).' ),
		array( 'key' => 'field_home_services_count', 'label' => 'Treatments to Show', 'name' => 'services_count', 'type' => 'number', 'default_value' => 0, 'min' => 0, 'max' => 24, 'instructions' => '0 = show all parent treatments.' ),
		array( 'key' => 'field_home_services_cta', 'label' => 'View All Button', 'name' => 'services_cta', 'type' => 'link' ),

		// ── YouTube / Guest Videos Tab ──
		array( 'key' => 'field_home_tab_youtube', 'label' => 'Guest Videos', 'type' => 'tab' ),
		array( 'key' => 'field_home_youtube_enable', 'label' => 'Enable Videos Section', 'name' => 'youtube_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_home_youtube_label', 'label' => 'Label', 'name' => 'youtube_label', 'type' => 'text', 'default_value' => 'Real Experiences' ),
		array( 'key' => 'field_home_youtube_heading', 'label' => 'Heading', 'name' => 'youtube_heading', 'type' => 'text', 'default_value' => 'Our Delighted Guests Says' ),
		array( 'key' => 'field_home_youtube_desc', 'label' => 'Description', 'name' => 'youtube_description', 'type' => 'textarea', 'rows' => 2, 'default_value' => 'Hear from our guests about their transformative Ayurvedic journeys at Sparsha — straight from their hearts.' ),
		array(
			'key' => 'field_home_youtube_videos', 'label' => 'Guest Videos (3x2 Grid - Up to 6 or more videos)', 'name' => 'youtube_videos', 'type' => 'repeater',
			'min' => 0, 'max' => 12, 'layout' => 'block', 'button_label' => 'Add Video',
			'sub_fields' => array(
				array( 'key' => 'field_home_yt_vid', 'label' => 'YouTube Video ID or URL', 'name' => 'video_id', 'type' => 'text', 'instructions' => 'Enter YouTube ID (e.g. MpjKyJEzNUQ) or full YouTube URL.' ),
				array( 'key' => 'field_home_yt_title', 'label' => 'Title / Caption (Optional)', 'name' => 'title', 'type' => 'text' ),
			),
		),
		array( 'key' => 'field_home_youtube_id', 'label' => 'Single Video ID (Fallback)', 'name' => 'youtube_video_id', 'type' => 'text', 'instructions' => 'Used if no videos are added in the repeater above.' ),
		array( 'key' => 'field_home_youtube_channel', 'label' => 'YouTube Channel URL', 'name' => 'youtube_channel_url', 'type' => 'url', 'default_value' => 'https://www.youtube.com/channel/UC_hTPa9a8_gnHSOKD1JMh5g' ),

		// ── Stay Healthy Tab ──
		array( 'key' => 'field_home_tab_healthy', 'label' => 'Stay Healthy', 'type' => 'tab' ),
		array( 'key' => 'field_home_healthy_enable', 'label' => 'Enable Section', 'name' => 'healthy_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_home_healthy_label', 'label' => 'Label', 'name' => 'healthy_label', 'type' => 'text', 'default_value' => 'Stay Healthy' ),
		array( 'key' => 'field_home_healthy_heading', 'label' => 'Heading', 'name' => 'healthy_heading', 'type' => 'text' ),
		array( 'key' => 'field_home_healthy_content', 'label' => 'Content', 'name' => 'healthy_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
		array( 'key' => 'field_home_healthy_image', 'label' => 'Image', 'name' => 'healthy_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_home_healthy_cta1', 'label' => 'Primary CTA', 'name' => 'healthy_cta_primary', 'type' => 'link' ),
		array( 'key' => 'field_home_healthy_cta2', 'label' => 'Secondary CTA', 'name' => 'healthy_cta_secondary', 'type' => 'link' ),

		// ── Welcome CTA Tab ──
		array( 'key' => 'field_home_tab_welcome', 'label' => 'Welcome CTA', 'type' => 'tab' ),
		array( 'key' => 'field_home_welcome_enable', 'label' => 'Enable Section', 'name' => 'welcome_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_home_welcome_heading', 'label' => 'Heading', 'name' => 'welcome_heading', 'type' => 'text' ),
		array( 'key' => 'field_home_welcome_subheading', 'label' => 'Sub-heading', 'name' => 'welcome_subheading', 'type' => 'text' ),
		array( 'key' => 'field_home_welcome_cta', 'label' => 'CTA Button', 'name' => 'welcome_cta', 'type' => 'link' ),

		// ── Panchakarma Tab ──
		array( 'key' => 'field_home_tab_panchakarma', 'label' => 'Panchakarma', 'type' => 'tab' ),
		array( 'key' => 'field_home_pancha_enable', 'label' => 'Enable Section', 'name' => 'panchakarma_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_home_pancha_label', 'label' => 'Label', 'name' => 'panchakarma_label', 'type' => 'text' ),
		array( 'key' => 'field_home_pancha_heading', 'label' => 'Heading', 'name' => 'panchakarma_heading', 'type' => 'text' ),
		array( 'key' => 'field_home_pancha_content', 'label' => 'Content', 'name' => 'panchakarma_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
		array( 'key' => 'field_home_pancha_cta', 'label' => 'CTA Button', 'name' => 'panchakarma_cta', 'type' => 'link' ),

		// ── Testimonials Tab ──
		array( 'key' => 'field_home_tab_testimonials', 'label' => 'Testimonials', 'type' => 'tab' ),
		array( 'key' => 'field_home_testimonials_enable', 'label' => 'Enable Testimonials', 'name' => 'testimonials_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_home_testimonials_label', 'label' => 'Label', 'name' => 'testimonials_label', 'type' => 'text' ),
		array( 'key' => 'field_home_testimonials_heading', 'label' => 'Heading', 'name' => 'testimonials_heading', 'type' => 'text' ),
		array( 'key' => 'field_home_testimonials_desc', 'label' => 'Description', 'name' => 'testimonials_description', 'type' => 'textarea', 'rows' => 2 ),
		array( 'key' => 'field_home_testimonials_shortcode', 'label' => 'Google Reviews Shortcode', 'name' => 'testimonials_shortcode', 'type' => 'textarea', 'rows' => 2, 'instructions' => 'Paste a reviews plugin shortcode (e.g. [grw_widget], [google-reviews]). If provided, replaces the default placeholder card.' ),
		array( 'key' => 'field_home_testimonials_link', 'label' => 'Reviews Link', 'name' => 'testimonials_link', 'type' => 'url', 'instructions' => 'External link shown in the fallback placeholder card.' ),

		// ── Facts Tab ──
		array( 'key' => 'field_home_tab_facts', 'label' => 'Interesting Facts', 'type' => 'tab' ),
		array( 'key' => 'field_home_facts_enable', 'label' => 'Enable Facts', 'name' => 'facts_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_home_facts_label', 'label' => 'Sub Title (Label)', 'name' => 'facts_label', 'type' => 'text', 'default_value' => 'Ancient Ayurvedic Wisdom' ),
		array( 'key' => 'field_home_facts_heading', 'label' => 'Section Title', 'name' => 'facts_heading', 'type' => 'text', 'default_value' => 'Interesting Facts' ),
		array(
			'key' => 'field_home_facts_items', 'label' => 'Facts', 'name' => 'facts_items', 'type' => 'repeater',
			'min' => 1, 'max' => 10, 'layout' => 'block',
			'sub_fields' => array(
				array( 'key' => 'field_fact_heading', 'label' => 'Heading', 'name' => 'heading', 'type' => 'text' ),
				array( 'key' => 'field_fact_content', 'label' => 'Content', 'name' => 'content', 'type' => 'textarea', 'rows' => 3 ),
				array( 'key' => 'field_fact_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array' ),
			),
		),

		// ── Founder Tab ──
		array( 'key' => 'field_home_tab_founder', 'label' => 'Founder', 'type' => 'tab' ),
		array( 'key' => 'field_home_founder_enable', 'label' => 'Enable Founder', 'name' => 'founder_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_home_founder_label', 'label' => 'Label', 'name' => 'founder_label', 'type' => 'text', 'default_value' => '✦ Trusted Experience' ),
		array( 'key' => 'field_home_founder_name', 'label' => 'Name', 'name' => 'founder_name', 'type' => 'text' ),
		array( 'key' => 'field_home_founder_role', 'label' => 'Role', 'name' => 'founder_role', 'type' => 'text', 'default_value' => 'Founder · Specialist' ),
		array( 'key' => 'field_home_founder_content', 'label' => 'Content', 'name' => 'founder_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
		array( 'key' => 'field_home_founder_image', 'label' => 'Photo', 'name' => 'founder_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_home_founder_quote', 'label' => 'Quote', 'name' => 'founder_quote', 'type' => 'textarea', 'rows' => 2 ),
		array(
			'key' => 'field_home_founder_tags', 'label' => 'Credential Tags', 'name' => 'founder_tags', 'type' => 'repeater',
			'min' => 0, 'max' => 6, 'layout' => 'table',
			'sub_fields' => array(
				array( 'key' => 'field_founder_tag_text', 'label' => 'Tag Text', 'name' => 'text', 'type' => 'text' ),
			),
		),
		array( 'key' => 'field_home_founder_cta', 'label' => 'CTA Button', 'name' => 'founder_cta', 'type' => 'link' ),

		// ── Journal Tab (section labels only — posts are pulled dynamically) ──
		array( 'key' => 'field_home_tab_journal', 'label' => 'Journal', 'type' => 'tab' ),
		array( 'key' => 'field_home_journal_enable', 'label' => 'Enable Journal', 'name' => 'journal_enable', 'type' => 'true_false', 'default_value' => 1, 'instructions' => 'Section auto-pulls latest published posts.' ),
		array( 'key' => 'field_home_journal_label', 'label' => 'Kicker Label', 'name' => 'journal_label', 'type' => 'text', 'default_value' => 'Ayurvedic Journal' ),
		array( 'key' => 'field_home_journal_heading', 'label' => 'Heading', 'name' => 'journal_heading', 'type' => 'text', 'default_value' => 'Latest Articles' ),
		array( 'key' => 'field_home_journal_desc', 'label' => 'Description', 'name' => 'journal_description', 'type' => 'textarea', 'rows' => 2 ),
		array( 'key' => 'field_home_journal_count', 'label' => 'Posts to Show', 'name' => 'journal_posts_count', 'type' => 'number', 'default_value' => 3, 'min' => 1, 'max' => 12 ),
	),
) );

// ═══════════════════════════════════════
// ABOUT PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_about_page',
	'title'    => 'About Page Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-about.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_about_tab_hero', 'label' => 'Hero', 'type' => 'tab' ),
		array( 'key' => 'field_about_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_about_hero_heading', 'label' => 'Heading', 'name' => 'hero_heading', 'type' => 'text' ),
		array( 'key' => 'field_about_hero_highlight', 'label' => 'Highlight Text', 'name' => 'hero_highlight', 'type' => 'text' ),

		array( 'key' => 'field_about_tab_philosophy', 'label' => 'Philosophy', 'type' => 'tab' ),
		array( 'key' => 'field_about_philosophy_enable', 'label' => 'Enable', 'name' => 'philosophy_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_about_philosophy_label', 'label' => 'Label', 'name' => 'philosophy_label', 'type' => 'text' ),
		array( 'key' => 'field_about_philosophy_heading', 'label' => 'Heading', 'name' => 'philosophy_heading', 'type' => 'text' ),
		array( 'key' => 'field_about_philosophy_content', 'label' => 'Content', 'name' => 'philosophy_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
		array( 'key' => 'field_about_philosophy_image', 'label' => 'Image', 'name' => 'philosophy_image', 'type' => 'image', 'return_format' => 'array' ),

		array( 'key' => 'field_about_tab_stats', 'label' => 'Stats', 'type' => 'tab' ),
		array( 'key' => 'field_about_stats_enable', 'label' => 'Enable', 'name' => 'stats_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array(
			'key' => 'field_about_stats_items', 'label' => 'Stats', 'name' => 'stats_items', 'type' => 'repeater',
			'min' => 1, 'max' => 6, 'layout' => 'table',
			'sub_fields' => array(
				array( 'key' => 'field_about_stat_number', 'label' => 'Number', 'name' => 'number', 'type' => 'text' ),
				array( 'key' => 'field_about_stat_suffix', 'label' => 'Suffix', 'name' => 'suffix', 'type' => 'text', 'instructions' => 'e.g. +, %' ),
				array( 'key' => 'field_about_stat_label', 'label' => 'Label', 'name' => 'label', 'type' => 'text' ),
			),
		),

		array( 'key' => 'field_about_tab_founder', 'label' => 'Founder', 'type' => 'tab' ),
		array( 'key' => 'field_about_founder_enable', 'label' => 'Enable', 'name' => 'founder_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_about_founder_image', 'label' => 'Photo', 'name' => 'founder_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
		array( 'key' => 'field_about_founder_label', 'label' => 'Label', 'name' => 'founder_label', 'type' => 'text' ),
		array( 'key' => 'field_about_founder_name', 'label' => 'Name', 'name' => 'founder_name', 'type' => 'text' ),
		array( 'key' => 'field_about_founder_content', 'label' => 'Content', 'name' => 'founder_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
		array( 'key' => 'field_about_founder_quote', 'label' => 'Quote', 'name' => 'founder_quote', 'type' => 'textarea', 'rows' => 2 ),
		array(
			'key' => 'field_about_founder_tags', 'label' => 'Credential Tags', 'name' => 'founder_tags', 'type' => 'repeater',
			'min' => 0, 'max' => 6, 'layout' => 'table',
			'sub_fields' => array(
				array( 'key' => 'field_about_ftag_text', 'label' => 'Tag', 'name' => 'text', 'type' => 'text' ),
			),
		),

		array( 'key' => 'field_about_tab_values', 'label' => 'Values', 'type' => 'tab' ),
		array( 'key' => 'field_about_values_enable', 'label' => 'Enable', 'name' => 'values_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_about_values_label', 'label' => 'Sub Title (Label)', 'name' => 'values_label', 'type' => 'text', 'default_value' => 'What We Stand For' ),
		array( 'key' => 'field_about_values_heading', 'label' => 'Section Title', 'name' => 'values_heading', 'type' => 'text', 'default_value' => 'Our Core Values' ),
		array(
			'key' => 'field_about_values_items', 'label' => 'Values', 'name' => 'values_items', 'type' => 'repeater',
			'min' => 1, 'max' => 6, 'layout' => 'block',
			'sub_fields' => array(
				array( 'key' => 'field_about_value_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_about_value_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
			),
		),

		array( 'key' => 'field_about_tab_team', 'label' => 'Team', 'type' => 'tab' ),
		array( 'key' => 'field_about_team_enable', 'label' => 'Enable Team Section', 'name' => 'team_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_about_team_label', 'label' => 'Kicker Label', 'name' => 'team_label', 'type' => 'text', 'default_value' => 'Meet Our Team' ),
		array( 'key' => 'field_about_team_heading', 'label' => 'Heading', 'name' => 'team_heading', 'type' => 'text', 'default_value' => 'The People Behind Sparsha' ),
		array( 'key' => 'field_about_team_desc', 'label' => 'Description', 'name' => 'team_description', 'type' => 'textarea', 'rows' => 2 ),
		array(
			'key' => 'field_about_team_members', 'label' => 'Team Members', 'name' => 'team_members', 'type' => 'repeater',
			'min' => 0, 'layout' => 'block', 'button_label' => 'Add Member',
			'sub_fields' => array(
				array( 'key' => 'field_about_member_image', 'label' => 'Photo', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
				array( 'key' => 'field_about_member_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text' ),
				array( 'key' => 'field_about_member_role', 'label' => 'Role', 'name' => 'role', 'type' => 'text' ),
				array( 'key' => 'field_about_member_bio', 'label' => 'Bio', 'name' => 'bio', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
			),
		),

		array( 'key' => 'field_about_tab_testimonials', 'label' => 'Testimonials', 'type' => 'tab' ),
		array( 'key' => 'field_about_testimonials_enable', 'label' => 'Enable', 'name' => 'testimonials_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_about_testimonials_shortcode', 'label' => 'Google Reviews Shortcode', 'name' => 'testimonials_shortcode', 'type' => 'textarea', 'rows' => 2, 'instructions' => 'Paste a reviews plugin shortcode (e.g. [grw_widget], [google-reviews]). If provided, replaces manual reviews.' ),
		array( 'key' => 'field_about_testimonials_link', 'label' => 'Reviews Link', 'name' => 'testimonials_link', 'type' => 'url', 'instructions' => 'External link shown in the fallback placeholder card.' ),
		array(
			'key' => 'field_about_testimonials_items', 'label' => 'Manual Reviews (Fallback)', 'name' => 'testimonials_items', 'type' => 'repeater',
			'min' => 0, 'max' => 6, 'layout' => 'block',
			'sub_fields' => array(
				array( 'key' => 'field_about_review_text', 'label' => 'Review', 'name' => 'text', 'type' => 'textarea', 'rows' => 3 ),
				array( 'key' => 'field_about_review_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text' ),
				array( 'key' => 'field_about_review_location', 'label' => 'Location', 'name' => 'location', 'type' => 'text' ),
			),
		),
	),
) );

// ═══════════════════════════════════════
// CONTACT PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_contact_page',
	'title'    => 'Contact Page Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-contact.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_contact_tab_hero', 'label' => 'Hero', 'type' => 'tab' ),
		array( 'key' => 'field_contact_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_contact_hero_heading', 'label' => 'Heading', 'name' => 'hero_heading', 'type' => 'text' ),

		array( 'key' => 'field_contact_tab_form', 'label' => 'Booking Form', 'type' => 'tab' ),
		array( 'key' => 'field_contact_form_heading', 'label' => 'Form Heading', 'name' => 'form_heading', 'type' => 'text', 'default_value' => 'Book Your Appointment' ),
		array( 'key' => 'field_contact_form_desc', 'label' => 'Form Description', 'name' => 'form_description', 'type' => 'text' ),
		array( 'key' => 'field_contact_booking_intro', 'label' => 'Booking Intro', 'name' => 'booking_intro', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0, 'instructions' => 'Intro text shown above the booking form.' ),
		array( 'key' => 'field_contact_form_shortcode', 'label' => 'Contact Form Shortcode', 'name' => 'contact_form_shortcode', 'type' => 'text', 'instructions' => "Paste the Contact Form 7 shortcode here. Example: [contact-form-7 id='123' title='Contact Form']" ),

		array( 'key' => 'field_contact_tab_map', 'label' => 'Map', 'type' => 'tab' ),
		array( 'key' => 'field_contact_map_enable', 'label' => 'Enable Map', 'name' => 'map_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_contact_map_embed', 'label' => 'Google Maps Embed Code / URL', 'name' => 'map_embed', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'Paste full Google Maps iframe embed code (e.g. <iframe src="..."></iframe>) or embed URL / share link.', 'default_value' => 'https://maps.google.com/maps?q=Bd.+Pipera+nr.+1-VIII+D,+Voluntari,+Romania&t=&z=15&ie=UTF8&iwloc=&output=embed' ),
	),
) );

// ═══════════════════════════════════════
// SERVICE PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_service_page',
	'title'    => 'Service Page Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-service.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_service_tab_hero', 'label' => 'Hero', 'type' => 'tab' ),
		array( 'key' => 'field_service_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_service_hero_tag', 'label' => 'Tag', 'name' => 'hero_tag', 'type' => 'text' ),
		array( 'key' => 'field_service_hero_heading', 'label' => 'Heading', 'name' => 'hero_heading', 'type' => 'text' ),
		array( 'key' => 'field_service_hero_highlight', 'label' => 'Highlight', 'name' => 'hero_highlight', 'type' => 'text' ),

		array( 'key' => 'field_service_tab_overview', 'label' => 'Overview', 'type' => 'tab' ),
		array( 'key' => 'field_service_overview_label', 'label' => 'Label', 'name' => 'overview_label', 'type' => 'text' ),
		array( 'key' => 'field_service_overview_heading', 'label' => 'Heading', 'name' => 'overview_heading', 'type' => 'text' ),
		array( 'key' => 'field_service_overview_content', 'label' => 'Content', 'name' => 'overview_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),

		array( 'key' => 'field_service_tab_sidebar', 'label' => 'Sidebar', 'type' => 'tab' ),
		array( 'key' => 'field_service_sidebar_duration', 'label' => 'Duration', 'name' => 'sidebar_duration', 'type' => 'text' ),
		array( 'key' => 'field_service_sidebar_who', 'label' => 'Who is it for', 'name' => 'sidebar_who', 'type' => 'text' ),
		array( 'key' => 'field_service_sidebar_includes', 'label' => 'Includes', 'name' => 'sidebar_includes', 'type' => 'text' ),
		array( 'key' => 'field_service_sidebar_pricing', 'label' => 'Pricing', 'name' => 'sidebar_pricing', 'type' => 'text' ),

		array( 'key' => 'field_service_tab_steps', 'label' => 'Steps', 'type' => 'tab' ),
		array( 'key' => 'field_service_steps_enable', 'label' => 'Enable', 'name' => 'steps_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array(
			'key' => 'field_service_steps_items', 'label' => 'Steps', 'name' => 'steps_items', 'type' => 'repeater',
			'min' => 1, 'max' => 8, 'layout' => 'block',
			'sub_fields' => array(
				array( 'key' => 'field_service_step_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_service_step_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 3 ),
			),
		),

		array( 'key' => 'field_service_tab_gallery', 'label' => 'Gallery', 'type' => 'tab' ),
		array( 'key' => 'field_service_gallery_enable', 'label' => 'Enable', 'name' => 'gallery_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_service_gallery_images', 'label' => 'Images', 'name' => 'gallery_images', 'type' => 'gallery', 'return_format' => 'array', 'instructions' => 'Upload 4 images for the gallery grid.' ),
	),
) );

// ═══════════════════════════════════════
// PRICELIST PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_pricelist_page',
	'title'    => 'Pricelist Page Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-pricelist.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_pricelist_tab_hero', 'label' => 'Hero', 'type' => 'tab' ),
		array( 'key' => 'field_pricelist_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_pricelist_hero_tag', 'label' => 'Hero Tag', 'name' => 'hero_tag', 'type' => 'text', 'default_value' => 'Holistic Wellness Menu' ),
		array( 'key' => 'field_pricelist_hero_heading', 'label' => 'Heading', 'name' => 'hero_heading', 'type' => 'text', 'default_value' => 'Treatments &' ),
		array( 'key' => 'field_pricelist_hero_highlight', 'label' => 'Heading Highlight', 'name' => 'hero_highlight', 'type' => 'text', 'default_value' => 'Price List' ),
		array( 'key' => 'field_pricelist_hero_cta', 'label' => 'Hero CTA', 'name' => 'hero_cta', 'type' => 'link' ),

		array( 'key' => 'field_pricelist_tab_treatments', 'label' => 'Treatments', 'type' => 'tab' ),
		array( 'key' => 'field_pricelist_intro_label', 'label' => 'Section Label', 'name' => 'treatments_label', 'type' => 'text', 'default_value' => 'Therapeutic Menu' ),
		array( 'key' => 'field_pricelist_intro_heading', 'label' => 'Section Heading', 'name' => 'treatments_heading', 'type' => 'text', 'default_value' => 'Ayurvedic Treatments & Pricing' ),
		array( 'key' => 'field_pricelist_intro_desc', 'label' => 'Section Description', 'name' => 'treatments_description', 'type' => 'textarea', 'rows' => 2 ),
		array( 'key' => 'field_pricelist_side_image', 'label' => 'Side Image', 'name' => 'treatments_side_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_pricelist_side_title', 'label' => 'Side Box Title', 'name' => 'side_title', 'type' => 'text', 'default_value' => 'Authentic Healing Touch' ),
		array( 'key' => 'field_pricelist_side_text', 'label' => 'Side Box Text', 'name' => 'side_text', 'type' => 'textarea', 'rows' => 2 ),
		array(
			'key' => 'field_pricelist_categories', 'label' => 'Treatment Categories', 'name' => 'treatment_categories', 'type' => 'repeater',
			'min' => 1, 'max' => 10, 'layout' => 'block',
			'sub_fields' => array(
				array( 'key' => 'field_pricelist_cat_icon', 'label' => 'Icon/Emoji', 'name' => 'icon', 'type' => 'text', 'instructions' => 'e.g. 📋 💆 ✨ 👁 📍' ),
				array( 'key' => 'field_pricelist_cat_title', 'label' => 'Category Title', 'name' => 'title', 'type' => 'text' ),
				array(
					'key' => 'field_pricelist_cat_items', 'label' => 'Treatments', 'name' => 'items', 'type' => 'repeater',
					'min' => 1, 'max' => 20, 'layout' => 'block',
					'sub_fields' => array(
						array( 'key' => 'field_pricelist_item_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text' ),
						array( 'key' => 'field_pricelist_item_duration', 'label' => 'Duration', 'name' => 'duration', 'type' => 'text' ),
						array( 'key' => 'field_pricelist_item_price', 'label' => 'Price', 'name' => 'price', 'type' => 'text' ),
						array( 'key' => 'field_pricelist_item_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 3 ),
						array( 'key' => 'field_pricelist_item_note', 'label' => 'Note', 'name' => 'note', 'type' => 'textarea', 'rows' => 2 ),
					),
				),
			),
		),
	),
) );

// ═══════════════════════════════════════
// GIFT VOUCHERS PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_gift_vouchers_page',
	'title'    => 'Gift Vouchers Page Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-gift-vouchers.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_vouchers_tab_hero', 'label' => 'Hero', 'type' => 'tab' ),
		array( 'key' => 'field_vouchers_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_vouchers_hero_tag', 'label' => 'Hero Tag', 'name' => 'hero_tag', 'type' => 'text', 'default_value' => 'Perfect Gift for Loved Ones' ),
		array( 'key' => 'field_vouchers_hero_heading', 'label' => 'Heading', 'name' => 'hero_heading', 'type' => 'text', 'default_value' => 'Gift Vouchers' ),
		array( 'key' => 'field_vouchers_hero_highlight', 'label' => 'Heading Highlight', 'name' => 'hero_highlight', 'type' => 'text', 'default_value' => 'of Relaxation' ),
		array( 'key' => 'field_vouchers_hero_cta', 'label' => 'Hero CTA', 'name' => 'hero_cta', 'type' => 'link' ),

		array( 'key' => 'field_vouchers_tab_intro', 'label' => 'Intro', 'type' => 'tab' ),
		array( 'key' => 'field_vouchers_intro_enable', 'label' => 'Enable', 'name' => 'intro_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_vouchers_intro_label', 'label' => 'Label', 'name' => 'intro_label', 'type' => 'text', 'default_value' => 'Give the Gift of Wellness' ),
		array( 'key' => 'field_vouchers_intro_heading', 'label' => 'Heading', 'name' => 'intro_heading', 'type' => 'text', 'default_value' => 'Looking for the perfect gift for your loved ones?' ),
		array( 'key' => 'field_vouchers_intro_content', 'label' => 'Content', 'name' => 'intro_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),

		array( 'key' => 'field_vouchers_tab_process', 'label' => 'Process', 'type' => 'tab' ),
		array( 'key' => 'field_vouchers_process_enable', 'label' => 'Enable', 'name' => 'process_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array(
			'key' => 'field_vouchers_process_steps', 'label' => 'Steps', 'name' => 'process_steps', 'type' => 'repeater',
			'min' => 1, 'max' => 8, 'layout' => 'block',
			'sub_fields' => array(
				array( 'key' => 'field_vouchers_step_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_vouchers_step_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
			),
		),

		array( 'key' => 'field_vouchers_tab_catalog', 'label' => 'Voucher Catalog', 'type' => 'tab' ),
		array( 'key' => 'field_vouchers_catalog_label', 'label' => 'Label', 'name' => 'catalog_label', 'type' => 'text', 'default_value' => 'Gift Catalog' ),
		array( 'key' => 'field_vouchers_catalog_heading', 'label' => 'Heading', 'name' => 'catalog_heading', 'type' => 'text', 'default_value' => 'Select Your Gift Voucher' ),
		array( 'key' => 'field_vouchers_catalog_desc', 'label' => 'Description', 'name' => 'catalog_description', 'type' => 'textarea', 'rows' => 2 ),
		array(
			'key' => 'field_vouchers_catalog_items', 'label' => 'Vouchers', 'name' => 'voucher_items', 'type' => 'repeater',
			'min' => 1, 'max' => 20, 'layout' => 'block',
			'sub_fields' => array(
				array( 'key' => 'field_voucher_item_tag', 'label' => 'Tag', 'name' => 'tag', 'type' => 'text', 'instructions' => 'e.g. Massage Ritual, Mind & Body' ),
				array( 'key' => 'field_voucher_item_duration', 'label' => 'Duration', 'name' => 'duration', 'type' => 'text' ),
				array( 'key' => 'field_voucher_item_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_voucher_item_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
				array( 'key' => 'field_voucher_item_price', 'label' => 'Price', 'name' => 'price', 'type' => 'text' ),
				array( 'key' => 'field_voucher_item_link', 'label' => 'Order Link', 'name' => 'link', 'type' => 'link' ),
				array( 'key' => 'field_voucher_item_featured', 'label' => 'Featured (Gold Card)', 'name' => 'featured', 'type' => 'true_false', 'default_value' => 0 ),
			),
		),

		array( 'key' => 'field_vouchers_tab_physical', 'label' => 'Physical Copy CTA', 'type' => 'tab' ),
		array( 'key' => 'field_vouchers_physical_enable', 'label' => 'Enable', 'name' => 'physical_enable', 'type' => 'true_false', 'default_value' => 1 ),
		array( 'key' => 'field_vouchers_physical_heading', 'label' => 'Heading', 'name' => 'physical_heading', 'type' => 'text' ),
		array( 'key' => 'field_vouchers_physical_content', 'label' => 'Content', 'name' => 'physical_content', 'type' => 'wysiwyg', 'toolbar' => 'basic', 'media_upload' => 0 ),
		array( 'key' => 'field_vouchers_physical_cta', 'label' => 'CTA Button', 'name' => 'physical_cta', 'type' => 'link' ),
	),
) );

// ═══════════════════════════════════════
// TREATMENT CPT FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_treatment_cpt',
	'title'    => 'Treatment Fields',
	'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'treatment' ) ) ),
	'menu_order' => 0,
	'fields'   => array(

		array( 'key' => 'field_treatment_tab_details', 'label' => 'Treatment Details', 'type' => 'tab' ),
		array( 'key' => 'field_treatment_badge', 'label' => 'Card Badge Text', 'name' => 'treatment_badge', 'type' => 'text', 'instructions' => 'Small badge shown on the card image (e.g. "Rooted in Wisdom. Made for You."). Leave blank for a default.' ),
		array( 'key' => 'field_treatment_subtitle', 'label' => 'Subtitle', 'name' => 'treatment_subtitle', 'type' => 'text', 'instructions' => 'Short subtitle shown below the title.' ),
		array( 'key' => 'field_treatment_duration', 'label' => 'Duration', 'name' => 'treatment_duration', 'type' => 'text', 'instructions' => 'e.g. 60 min, 90 min, 2.5 hrs' ),
		array( 'key' => 'field_treatment_price', 'label' => 'Price', 'name' => 'treatment_price', 'type' => 'text', 'instructions' => 'e.g. 300 Lei, 300-400 Lei' ),
		array( 'key' => 'field_treatment_short_desc', 'label' => 'Short Description', 'name' => 'treatment_short_description', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'Brief description for cards and listings.' ),
		array( 'key' => 'field_treatment_card_body', 'label' => 'Card Body', 'name' => 'treatment_card_body', 'type' => 'textarea', 'rows' => 4, 'instructions' => 'Paragraph shown on the treatment card. When empty, the card falls back to listing child treatments.' ),

		array( 'key' => 'field_treatment_tab_content', 'label' => 'Full Content', 'type' => 'tab' ),
		array( 'key' => 'field_treatment_content', 'label' => 'Detailed Description', 'name' => 'treatment_content', 'type' => 'wysiwyg', 'toolbar' => 'full', 'media_upload' => 1, 'instructions' => 'Full treatment description shown on the single treatment page.' ),
		array( 'key' => 'field_treatment_note', 'label' => 'Special Note', 'name' => 'treatment_note', 'type' => 'textarea', 'rows' => 2, 'instructions' => 'Optional note shown in a highlighted box (e.g. pricing tiers, recommendations).' ),

		array( 'key' => 'field_treatment_tab_pricing', 'label' => 'Price Variants', 'type' => 'tab' ),
		array(
			'key' => 'field_treatment_price_variants', 'label' => 'Price Variants', 'name' => 'treatment_price_variants', 'type' => 'repeater',
			'min' => 0, 'max' => 6, 'layout' => 'table', 'instructions' => 'Add multiple price/duration options if the treatment has variants.',
			'sub_fields' => array(
				array( 'key' => 'field_treatment_pv_duration', 'label' => 'Duration', 'name' => 'duration', 'type' => 'text' ),
				array( 'key' => 'field_treatment_pv_price', 'label' => 'Price', 'name' => 'price', 'type' => 'text' ),
				array( 'key' => 'field_treatment_pv_note', 'label' => 'Note', 'name' => 'note', 'type' => 'text' ),
			),
		),

		array( 'key' => 'field_treatment_tab_therapies', 'label' => 'Therapies List', 'type' => 'tab' ),
		array(
			'key' => 'field_treatment_therapies', 'label' => 'Therapies', 'name' => 'treatment_therapies', 'type' => 'repeater',
			'min' => 0, 'layout' => 'block', 'button_label' => 'Add Therapy',
			'instructions' => 'Optional. When items are added, they render as alternating image/content rows and replace the main WYSIWYG content on the single-treatment page.',
			'sub_fields' => array(
				array( 'key' => 'field_therapy_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
				array( 'key' => 'field_therapy_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_therapy_meta', 'label' => 'Duration / Price', 'name' => 'meta', 'type' => 'text', 'instructions' => 'e.g. 70 min: 300 Lei | 100 min: 400 Lei' ),
				array( 'key' => 'field_therapy_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 4 ),
				array( 'key' => 'field_therapy_cta', 'label' => 'CTA Link', 'name' => 'cta', 'type' => 'link', 'instructions' => 'Optional. Leave empty to hide the button.' ),
			),
		),

		array( 'key' => 'field_treatment_tab_gallery', 'label' => 'Gallery', 'type' => 'tab' ),
		array( 'key' => 'field_treatment_gallery', 'label' => 'Treatment Gallery', 'name' => 'treatment_gallery', 'type' => 'gallery', 'return_format' => 'array', 'instructions' => 'Upload treatment images.' ),

		array( 'key' => 'field_treatment_tab_booking', 'label' => 'Booking', 'type' => 'tab' ),
		array( 'key' => 'field_treatment_booking_link', 'label' => 'Booking Link', 'name' => 'treatment_booking_link', 'type' => 'link', 'instructions' => 'Custom booking link. Defaults to contact page if empty.' ),
	),
) );

// ═══════════════════════════════════════
// PHOTO GALLERY PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_photo_gallery_page',
	'title'    => 'Photo Gallery Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-photo-gallery.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_pg_tab_hero', 'label' => 'Hero', 'type' => 'tab' ),
		array( 'key' => 'field_pg_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_pg_heading', 'label' => 'Page Heading', 'name' => 'gallery_heading', 'type' => 'text', 'default_value' => 'Photo Gallery' ),

		array( 'key' => 'field_pg_tab_gallery', 'label' => 'Gallery', 'type' => 'tab' ),
		array( 'key' => 'field_pg_label', 'label' => 'Section Label', 'name' => 'gallery_label', 'type' => 'text', 'default_value' => 'Our Centre & Treatments' ),
		array( 'key' => 'field_pg_subheading', 'label' => 'Section Heading', 'name' => 'gallery_subheading', 'type' => 'text', 'default_value' => 'Moments of Healing' ),
		array( 'key' => 'field_pg_images', 'label' => 'Photos', 'name' => 'gallery_images', 'type' => 'gallery', 'return_format' => 'array', 'insert' => 'append', 'instructions' => 'Upload photos. They display in a masonry grid and open full-size on click.' ),
	),
) );

// ═══════════════════════════════════════
// VIDEO GALLERY PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_video_gallery_page',
	'title'    => 'Video Gallery Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-video-gallery.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_vg_tab_hero', 'label' => 'Hero', 'type' => 'tab' ),
		array( 'key' => 'field_vg_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_vg_heading', 'label' => 'Page Heading', 'name' => 'gallery_heading', 'type' => 'text', 'default_value' => 'Video Gallery' ),

		array( 'key' => 'field_vg_tab_videos', 'label' => 'Videos', 'type' => 'tab' ),
		array( 'key' => 'field_vg_label', 'label' => 'Section Label', 'name' => 'gallery_label', 'type' => 'text', 'default_value' => 'Watch & Learn' ),
		array( 'key' => 'field_vg_subheading', 'label' => 'Section Heading', 'name' => 'gallery_subheading', 'type' => 'text', 'default_value' => 'Guest Stories & Ayurvedic Wisdom' ),
		array(
			'key' => 'field_vg_videos', 'label' => 'Videos', 'name' => 'videos', 'type' => 'repeater',
			'min' => 1, 'max' => 30, 'layout' => 'block', 'button_label' => 'Add Video',
			'sub_fields' => array(
				array( 'key' => 'field_vg_video_id', 'label' => 'YouTube Video ID', 'name' => 'youtube_id', 'type' => 'text', 'instructions' => 'Just the ID, e.g. MpjKyJEzNUQ (from youtube.com/watch?v=MpjKyJEzNUQ)' ),
				array( 'key' => 'field_vg_video_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
			),
		),
		array( 'key' => 'field_vg_channel', 'label' => 'YouTube Channel URL', 'name' => 'youtube_channel_url', 'type' => 'url', 'instructions' => 'Leave blank to use the global channel link from Customizer.' ),
	),
) );

// ═══════════════════════════════════════
// FAQ PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_faq_page',
	'title'    => 'FAQ Page Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-faq.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_faqp_tab_hero', 'label' => 'Hero', 'type' => 'tab' ),
		array( 'key' => 'field_faqp_hero_image', 'label' => 'Hero Image', 'name' => 'hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_faqp_heading', 'label' => 'Page Heading', 'name' => 'faq_heading', 'type' => 'text', 'default_value' => 'Frequently Asked Questions' ),

		array( 'key' => 'field_faqp_tab_faqs', 'label' => 'Questions', 'type' => 'tab' ),
		array( 'key' => 'field_faqp_label', 'label' => 'Section Label', 'name' => 'faq_label', 'type' => 'text', 'default_value' => 'Common Questions' ),
		array( 'key' => 'field_faqp_subheading', 'label' => 'Section Heading', 'name' => 'faq_subheading', 'type' => 'text', 'default_value' => 'Everything You Need to Know' ),
		array(
			'key' => 'field_faqp_items', 'label' => 'Questions', 'name' => 'faq_items', 'type' => 'repeater',
			'min' => 1, 'max' => 50, 'layout' => 'block', 'button_label' => 'Add Question',
			'sub_fields' => array(
				array( 'key' => 'field_faqp_q', 'label' => 'Question', 'name' => 'question', 'type' => 'text' ),
				array( 'key' => 'field_faqp_a', 'label' => 'Answer', 'name' => 'answer', 'type' => 'textarea', 'rows' => 4 ),
			),
		),
	),
) );

// ═══════════════════════════════════════
// PRODUCTS PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_product_page',
	'title'    => 'Products Page Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-product.php' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_product_banner', 'label' => 'Product Banner', 'name' => 'product_banner', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Upload the full-width banner image. It displays edge-to-edge on the Products page.' ),
	),
) );

// ═══════════════════════════════════════
// BLOG / ARCHIVE OPTIONS PAGE (theme-level settings)
// ═══════════════════════════════════════
if ( function_exists( 'acf_add_options_page' ) ) {
	acf_add_options_page( array(
		'page_title'  => 'Theme Content Options',
		'menu_title'  => 'Theme Options',
		'menu_slug'   => 'sparsha-theme-options',
		'capability'  => 'manage_options',
		'redirect'    => false,
		'icon_url'    => 'dashicons-admin-generic',
		'position'    => 60,
	) );
}

acf_add_local_field_group( array(
	'key'      => 'group_theme_content_options',
	'title'    => 'Blog / Archives',
	'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'sparsha-theme-options' ) ) ),
	'fields'   => array(
		array( 'key' => 'field_opt_tab_blog', 'label' => 'Blog Index', 'type' => 'tab' ),
		array( 'key' => 'field_opt_blog_heading', 'label' => 'Heading', 'name' => 'blog_index_heading', 'type' => 'text', 'default_value' => 'Journal & Wellness Articles' ),
		array( 'key' => 'field_opt_blog_sub', 'label' => 'Subheading', 'name' => 'blog_index_sub', 'type' => 'textarea', 'rows' => 2, 'default_value' => 'Insights, tips, and stories from the world of Ayurveda.' ),
		array( 'key' => 'field_opt_blog_banner', 'label' => 'Banner Image', 'name' => 'blog_index_banner', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Falls back to default page banner if empty.' ),

		array( 'key' => 'field_opt_tab_single', 'label' => 'Single Post', 'type' => 'tab' ),
		array( 'key' => 'field_opt_single_default_img', 'label' => 'Default Featured Image', 'name' => 'single_post_default_image', 'type' => 'image', 'return_format' => 'array', 'instructions' => 'Shown when a post has no featured image.' ),
		array( 'key' => 'field_opt_related_heading', 'label' => 'Related Posts Heading', 'name' => 'related_posts_heading', 'type' => 'text', 'default_value' => 'You May Also Enjoy' ),

		array( 'key' => 'field_opt_tab_treatment_archive', 'label' => 'Treatment Archive', 'type' => 'tab' ),
		array( 'key' => 'field_opt_tr_arch_label', 'label' => 'Kicker Label', 'name' => 'treatment_archive_label', 'type' => 'text', 'default_value' => 'Our Treatments' ),
		array( 'key' => 'field_opt_tr_arch_heading', 'label' => 'Heading', 'name' => 'treatment_archive_heading', 'type' => 'text', 'default_value' => 'Ayurvedic Treatments' ),
		array( 'key' => 'field_opt_tr_arch_sub', 'label' => 'Subheading', 'name' => 'treatment_archive_sub', 'type' => 'textarea', 'rows' => 2, 'default_value' => 'A comprehensive range of classical Ayurvedic therapies tailored to your unique constitution and needs.' ),
		array( 'key' => 'field_opt_tr_arch_banner', 'label' => 'Banner Image', 'name' => 'treatment_archive_banner', 'type' => 'image', 'return_format' => 'array' ),
	),
) );

// ═══════════════════════════════════════
// AUTHENTIC PANCHAKARMA RETREAT PAGE FIELDS
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_authentic_panchakarma_retreat',
	'title'    => 'Authentic Panchakarma Retreat Fields',
	'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-authentic-panchakarma-retreat.php' ) ) ),
	'menu_order' => 0,
	'fields'   => array(

		// ── Hero Tab ──
		array( 'key' => 'field_pancha_tab_hero', 'label' => 'Hero Slider', 'type' => 'tab' ),
		array( 'key' => 'field_pancha_hero_title', 'label' => 'Default Title (Fallback)', 'name' => 'pancha_hero_title', 'type' => 'text', 'default_value' => 'Authentic Panchakarma Retreat' ),
		array( 'key' => 'field_pancha_hero_image', 'label' => 'Default Image (Fallback)', 'name' => 'pancha_hero_image', 'type' => 'image', 'return_format' => 'array' ),
		array(
			'key' => 'field_pancha_hero_slides', 'label' => 'Hero Slides', 'name' => 'pancha_hero_slides', 'type' => 'repeater',
			'layout' => 'block', 'button_label' => 'Add Slide',
			'sub_fields' => array(
				array( 'key' => 'field_pancha_slide_image', 'label' => 'Background Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array' ),
				array( 'key' => 'field_pancha_slide_title', 'label' => 'Title / Heading', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_pancha_slide_highlight', 'label' => 'Heading Highlight', 'name' => 'highlight', 'type' => 'text' ),
				array( 'key' => 'field_pancha_slide_highlight_color', 'label' => 'Highlight Text Color', 'name' => 'highlight_color', 'type' => 'color_picker', 'default_value' => '#fcd34d' ),
				array( 'key' => 'field_pancha_slide_subtitle', 'label' => 'Subtitle / Label', 'name' => 'subtitle', 'type' => 'text' ),
				array( 'key' => 'field_pancha_slide_cta1', 'label' => 'Primary CTA', 'name' => 'cta_primary', 'type' => 'link' ),
			),
		),

		// ── Intro Section Tab ──
		array( 'key' => 'field_pancha_tab_intro', 'label' => 'Intro Section', 'type' => 'tab' ),
		array( 'key' => 'field_pancha_intro_image', 'label' => 'Left Image', 'name' => 'pancha_intro_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_pancha_intro_desc', 'label' => 'Intro Content', 'name' => 'pancha_intro_description', 'type' => 'wysiwyg', 'toolbar' => 'full' ),

		// ── Facilities Tab ──
		array( 'key' => 'field_pancha_tab_facilities', 'label' => 'Facilities', 'type' => 'tab' ),
		array( 'key' => 'field_pancha_facilities_heading', 'label' => 'Heading', 'name' => 'pancha_facilities_heading', 'type' => 'text', 'default_value' => 'Our Facilities & Accommodations' ),
		array( 'key' => 'field_pancha_facilities_subheading', 'label' => 'Subheading', 'name' => 'pancha_facilities_subheading', 'type' => 'textarea', 'rows' => 2 ),
		array(
			'key' => 'field_pancha_facilities_items', 'label' => 'Facility Items', 'name' => 'pancha_facilities_items', 'type' => 'repeater',
			'layout' => 'block', 'button_label' => 'Add Facility',
			'sub_fields' => array(
				array( 'key' => 'field_pancha_facility_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
				array( 'key' => 'field_pancha_facility_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_pancha_facility_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 3 ),
				array( 'key' => 'field_pancha_facility_note', 'label' => 'Note / Subtext', 'name' => 'note', 'type' => 'text' ),
			),
		),

		// ── Journey Tab ──
		array( 'key' => 'field_pancha_tab_journey', 'label' => 'Healing Journey', 'type' => 'tab' ),
		array( 'key' => 'field_pancha_journey_heading', 'label' => 'Heading', 'name' => 'pancha_journey_heading', 'type' => 'text', 'default_value' => 'The Panchakarma Healing Journey' ),
		array( 'key' => 'field_pancha_journey_question', 'label' => 'Question Subtitle', 'name' => 'pancha_journey_question', 'type' => 'text', 'default_value' => 'Why Should One Consider Undergoing Ayurvedic Detox Therapy (Panchakarma)?' ),
		array( 'key' => 'field_pancha_journey_image', 'label' => 'Section Image', 'name' => 'pancha_journey_image', 'type' => 'image', 'return_format' => 'array' ),
		array( 'key' => 'field_pancha_journey_content', 'label' => 'Main Content', 'name' => 'pancha_journey_content', 'type' => 'wysiwyg', 'toolbar' => 'full' ),
		array( 'key' => 'field_pancha_dosha_title', 'label' => 'Dosha Subsection Title', 'name' => 'pancha_dosha_title', 'type' => 'text', 'default_value' => "Here's how the doshas are relevant in Panchakarma treatment" ),
		array(
			'key' => 'field_pancha_dosha_steps', 'label' => 'Dosha Steps', 'name' => 'pancha_dosha_steps', 'type' => 'repeater',
			'layout' => 'block', 'button_label' => 'Add Dosha Step',
			'sub_fields' => array(
				array( 'key' => 'field_pancha_dstep_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_pancha_dstep_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 3 ),
			),
		),
		array( 'key' => 'field_pancha_journey_quote', 'label' => 'Summary Statement', 'name' => 'pancha_journey_quote', 'type' => 'textarea', 'rows' => 3 ),

		// ── Program Tab ──
		array( 'key' => 'field_pancha_tab_program', 'label' => 'Our Program', 'type' => 'tab' ),
		array( 'key' => 'field_pancha_program_heading', 'label' => 'Heading', 'name' => 'pancha_program_heading', 'type' => 'text', 'default_value' => 'Our Program' ),
		array(
			'key' => 'field_pancha_program_items', 'label' => 'Program Items', 'name' => 'pancha_program_items', 'type' => 'repeater',
			'layout' => 'block', 'button_label' => 'Add Item',
			'sub_fields' => array(
				array( 'key' => 'field_pancha_pitem_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
				array( 'key' => 'field_pancha_pitem_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_pancha_pitem_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 3 ),
			),
		),

		// ── Benefits Tab ──
		array( 'key' => 'field_pancha_tab_benefits', 'label' => 'Health Benefits', 'type' => 'tab' ),
		array( 'key' => 'field_pancha_benefits_heading', 'label' => 'Heading', 'name' => 'pancha_benefits_heading', 'type' => 'text', 'default_value' => 'Transformational Health Benefits' ),
		array(
			'key' => 'field_pancha_benefits_items', 'label' => 'Benefit List', 'name' => 'pancha_benefits_items', 'type' => 'repeater',
			'layout' => 'block', 'button_label' => 'Add Benefit',
			'sub_fields' => array(
				array( 'key' => 'field_pancha_bitem_title', 'label' => 'Title / Bullet', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_pancha_bitem_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
			),
		),

		// ── Pricing Tab ──
		array( 'key' => 'field_pancha_tab_pricing', 'label' => 'Retreat Options & Rates', 'type' => 'tab' ),
		array( 'key' => 'field_pancha_pricing_heading', 'label' => 'Heading', 'name' => 'pancha_pricing_heading', 'type' => 'text', 'default_value' => 'Flexible Retreat Options' ),
		array( 'key' => 'field_pancha_pricing_intro', 'label' => 'Intro Text', 'name' => 'pancha_pricing_intro', 'type' => 'textarea', 'rows' => 3 ),
		array( 'key' => 'field_pancha_pricing_note', 'label' => 'Note', 'name' => 'pancha_pricing_note', 'type' => 'textarea', 'rows' => 2 ),
		array(
			'key' => 'field_pancha_pricing_packages', 'label' => 'Retreat Packages', 'name' => 'pancha_pricing_packages', 'type' => 'repeater',
			'layout' => 'block', 'button_label' => 'Add Package',
			'sub_fields' => array(
				array( 'key' => 'field_pancha_pkg_title', 'label' => 'Duration / Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_pancha_pkg_subtitle', 'label' => 'Subtitle', 'name' => 'subtitle', 'type' => 'text', 'default_value' => 'Detox (Panchakarma Therapy)' ),
				array( 'key' => 'field_pancha_pkg_rate', 'label' => 'Rate', 'name' => 'rate', 'type' => 'text', 'default_value' => '€100/day' ),
				array( 'key' => 'field_pancha_pkg_total', 'label' => 'Total per person', 'name' => 'total', 'type' => 'text' ),
			),
		),
		array(
			'key' => 'field_pancha_pricing_addons', 'label' => 'Accommodation & Food Add-ons', 'name' => 'pancha_pricing_addons', 'type' => 'repeater',
			'layout' => 'block', 'button_label' => 'Add Add-on',
			'sub_fields' => array(
				array( 'key' => 'field_pancha_addon_title', 'label' => 'Item Name', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_pancha_addon_rate', 'label' => 'Rate', 'name' => 'rate', 'type' => 'text' ),
				array( 'key' => 'field_pancha_addon_note', 'label' => 'Note', 'name' => 'note', 'type' => 'text' ),
			),
		),

		// ── Call to Action Tab ──
		array( 'key' => 'field_pancha_tab_cta', 'label' => 'Call to Action', 'type' => 'tab' ),
		array( 'key' => 'field_pancha_cta_heading', 'label' => 'Heading', 'name' => 'pancha_cta_heading', 'type' => 'text', 'default_value' => 'Start Your Ayurveda Journey Today' ),
		array( 'key' => 'field_pancha_cta_vision', 'label' => 'Vision Statement', 'name' => 'pancha_cta_vision', 'type' => 'textarea', 'rows' => 3 ),
		array( 'key' => 'field_pancha_cta_desc', 'label' => 'Description', 'name' => 'pancha_cta_desc', 'type' => 'textarea', 'rows' => 3 ),
	),
) );

// ═══════════════════════════════════════
// GLOBAL PAGE BANNER OVERRIDE (all pages + treatments + posts)
// ═══════════════════════════════════════
acf_add_local_field_group( array(
	'key'      => 'group_page_banner',
	'title'    => 'Page Banner',
	'position' => 'side',
	'location' => array(
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ),
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'treatment' ) ),
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ),
	),
	'fields'   => array(
		array(
			'key'           => 'field_page_banner_image',
			'label'         => 'Page Banner Image',
			'name'          => 'page_banner_image',
			'type'          => 'image',
			'return_format' => 'array',
			'preview_size'  => 'medium',
			'instructions'  => 'Override the default banner set in Customizer → Branding → Default Page Banner. Leave empty to use the default.',
		),
	),
) );
