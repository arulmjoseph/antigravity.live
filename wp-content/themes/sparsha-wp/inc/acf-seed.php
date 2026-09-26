<?php
/**
 * ACF Content Seeder
 * Run: visit ?acf_seed=1 as admin, or: wp eval-file inc/acf-seed.php
 */

add_action( 'init', function() {
	if ( ! isset( $_GET['acf_seed'] ) || $_GET['acf_seed'] !== '1' ) return;
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Admin only.' );
	sparsha_seed_acf_content();
	echo '<p>ACF seeding complete.</p>';
	exit;
} );

function sparsha_seed_acf_content() {
	if ( ! function_exists( 'update_field' ) ) {
		echo "ACF not active.\n";
		return;
	}

	$log = function( $msg ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::log( $msg );
		} else {
			echo "<p>{$msg}</p>";
		}
	};

	$by_template = function( $tpl ) {
		$r = get_posts( array( 'post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => $tpl, 'posts_per_page' => 1, 'fields' => 'ids' ) );
		return $r ? (int) $r[0] : 0;
	};

	// ═══════════════════════════════════════
	// HOME PAGE
	// ═══════════════════════════════════════
	$home_id = (int) get_option( 'page_on_front' );
	if ( $home_id ) {
		$log( "Seeding Home page (ID {$home_id})..." );

		update_field( 'hero_enable', 1, $home_id );
		update_field( 'hero_slides', array(
			array( 'label' => 'Authentic Ayurveda · Budapest, Hungary', 'heading' => 'Detox Your Body', 'highlight' => 'with Ayurveda', 'description' => 'Rooted in the ancient traditions of Kerala — bringing over 12 years of authentic healing to the heart of Europe.' ),
			array( 'label' => 'Authentic Ayurveda · Budapest, Hungary', 'heading' => 'Experience the', 'highlight' => 'Bliss of Healing', 'description' => 'Traditional Kati Basti and warm herbal oil therapies — deeply restorative, deeply balancing.' ),
			array( 'label' => 'Authentic Ayurveda · Budapest, Hungary', 'heading' => 'Authentic Ayurveda', 'highlight' => 'from Its Homeland', 'description' => 'Shirodhara and Panchakarma — the crown jewels of Kerala\'s ancient healing science, now in Budapest.' ),
			array( 'label' => 'Authentic Ayurveda · Budapest, Hungary', 'heading' => 'A Decade of Trust in', 'highlight' => 'European Ayurvedic Care', 'description' => 'Over 12 years of authentic Kerala Ayurveda — trusted by thousands across Europe for genuine healing and lasting results.' ),
		), $home_id );

		update_field( 'about_enable', 1, $home_id );
		update_field( 'about_label', 'About Sparsha', $home_id );
		update_field( 'about_heading', 'Why You Choose Sparsha Ayurveda!', $home_id );
		update_field( 'about_content', '<p>For over <strong>12 years</strong>, Sparsha has been dedicated to offering authentic Ayurvedic treatments &amp; massages in Europe, delighting our clients with exceptional care and results.</p><p>We are from <strong>Kerala</strong>, southern part of India — the homeland of Ayurveda — and we are excited to bring the ancient healing traditions of Ayurveda to Budapest.</p><p>These precious herbs form the foundation of our traditional Ayurvedic treatments, each carefully selected for its unique healing properties. Our treatments aim to balance mind, body, and spirit for overall well-being.</p>', $home_id );
		update_field( 'about_badge_number', '12+', $home_id );
		update_field( 'about_badge_text', 'Years in Europe', $home_id );

		update_field( 'services_enable', 1, $home_id );
		update_field( 'services_label', 'Our Treatments', $home_id );
		update_field( 'services_heading', 'Experience the Bliss of Ayurveda With Us!', $home_id );
		update_field( 'services_description', 'Revitalizing the mind, body, and spirit with authentic Ayurveda — each treatment tailored to your unique constitution (Prakriti).', $home_id );
		update_field( 'services_items', array(
			array( 'title' => 'Ayurveda Consultation', 'description' => 'An Ayurvedic consultation goes beyond symptoms to uncover the root cause. Through ancient wisdom, we provide a clear roadmap for restoring balance (Doshas) and vitality.' ),
			array( 'title' => 'Ayurveda Massage Therapy', 'description' => 'A timeless journey to deep well-being. Warm, herbal-infused oils cleanse, nourish, and rebalance your vital energies (doshas), restoring harmony to mind, body, and spirit.' ),
			array( 'title' => 'Panchakarma Treatment', 'description' => 'A powerful journey of cellular detoxification and renewal. This time-tested treatment meticulously cleanses the body and mind, resulting in optimal health, clarity, and holistic well-being.' ),
			array( 'title' => 'Ayurveda Cure', 'description' => 'A time-honoured system that doesn\'t just treat symptoms but seeks the root cause of imbalance — a holistic path to lasting health, tailored to your unique constitution (Prakriti).' ),
			array( 'title' => 'Women Care', 'description' => 'Balancing the key energies (Doshas), these therapies soothe the nervous system, address the root cause of imbalances, and empower you to live in graceful alignment with your body\'s innate rhythms.' ),
			array( 'title' => 'Baby & Mother Care', 'description' => 'A holistic program for pregnancy & beyond — gentle massages, herbal formulas, and dietary advice to promote the health of both mother and baby.' ),
			array( 'title' => 'Infertility Treatment', 'description' => 'A holistic and gentle path to parenthood — strengthening reproductive tissues (Shukra Dhatu), balancing hormones, optimizing digestion (Agni), and enhancing natural fertility for both partners.' ),
			array( 'title' => 'Joint Pain & Muscular Disorder', 'description' => 'Powerful natural solutions for arthritis, sciatica, and chronic back pain. Through detoxification, herbal remedies, and specialised oil treatments, we reduce pain and promote long-term musculoskeletal health.' ),
			array( 'title' => 'Healthy Slimming', 'description' => 'Achieve balanced and healthy weight loss naturally. Personalised therapies detoxify, improve circulation, break down fat deposits, and establish sustainable habits for a lighter, more energetic you.' ),
		), $home_id );

		update_field( 'youtube_enable', 1, $home_id );
		update_field( 'youtube_label', 'Real Experiences', $home_id );
		update_field( 'youtube_heading', 'Our Delighted Guests Says', $home_id );
		update_field( 'youtube_description', 'Hear from our guests about their transformative Ayurvedic journeys at Sparsha — straight from their hearts.', $home_id );
		update_field( 'youtube_video_id', 'MpjKyJEzNUQ', $home_id );
		update_field( 'youtube_channel_url', 'https://www.youtube.com/channel/UC_hTPa9a8_gnHSOKD1JMh5g', $home_id );

		update_field( 'healthy_enable', 1, $home_id );
		update_field( 'healthy_label', 'Stay Healthy', $home_id );
		update_field( 'healthy_heading', 'With Ayurveda Treatment At Sparsha Ayurveda', $home_id );
		update_field( 'healthy_content', '<p>Step into Sparsha Ayurveda and allow the ancient wisdom and artistry of Ayurveda to embrace you, nurturing your body, calming your mind, and uplifting your spirit.</p><p>At Sparsha Ayurveda, the essence of the Sanskrit word <em>"Sparsha"</em> resonates deeply — embodying the profound healing touch that reaches beyond the physical realm.</p><p>Sparsha Ayurveda centre is in the heart of Budapest. Our centre embraces Indian traditions in a contemporary setting, using exclusively imported equipment from India while maintaining its traditional essence.</p><p>Our team of Ayurvedic specialists provides you with personalised guidance on various Ayurvedic treatments and Yoga practices that effectively enhance your lifestyle.</p>', $home_id );

		update_field( 'welcome_enable', 1, $home_id );
		update_field( 'welcome_heading', 'We eagerly await the opportunity to welcome you to Sparsha', $home_id );
		update_field( 'welcome_subheading', 'and assist you on your journey to well-being.', $home_id );

		update_field( 'panchakarma_enable', 1, $home_id );
		update_field( 'panchakarma_label', 'Panchakarma & Overall Wellness', $home_id );
		update_field( 'panchakarma_heading', 'Why Should One Consider Undergoing Ayurvedic Detox Therapy (Panchakarma)?', $home_id );
		update_field( 'panchakarma_content', '<p>Ayurveda is an ancient Indian system of medicine that focuses on achieving balance and harmony in the body, mind, and spirit.</p><p>Panchakarma is the method of purifying the impurities from the human body. The word panchakarma means <strong>Pancha</strong> (five) <strong>Karma</strong> (procedures/treatments), which means 5 treatments for the cleansing of our body.</p><p>By undergoing Panchakarma, the body is cleansed of all toxic substances resulting in enhanced health, vitality, and Vigor.</p><p class="text-forest-700 font-medium">We envision a community where every individual is blessed with good health and happiness.</p>', $home_id );

		update_field( 'testimonials_enable', 1, $home_id );
		update_field( 'testimonials_label', 'What Our Guests Say', $home_id );
		update_field( 'testimonials_heading', 'Testimonials', $home_id );
		update_field( 'testimonials_description', 'Read what our guests say about their healing experience at Sparsha Ayurveda Centre.', $home_id );

		update_field( 'facts_enable', 1, $home_id );
		update_field( 'facts_label', 'Ancient Ayurvedic Wisdom', $home_id );
		update_field( 'facts_heading', 'Interesting Facts', $home_id );
		update_field( 'facts_items', array(
			array( 'heading' => 'Opposing Foods Cause Disease', 'content' => 'Consuming opposing natural foods such as milk and sour or salty foods; fish and milk; and cold and hot foods together leads to diseases such as allergies, eczema, and diabetes. You should not eat curd at night as it aggravates Kapha.' ),
			array( 'heading' => 'Your Tongue is a Map to Your Inner Health', 'content' => 'In Ayurveda, diagnosis often begins with looking at the tongue. Practitioners believe your tongue reflects the state of your entire digestive system and internal organs.' ),
			array( 'heading' => 'Daytime Sleep & Internal Harm', 'content' => 'As per Ayurveda, sleeping for more than 20 minutes during the day causes serious harm to the internal body functions.' ),
			array( 'heading' => 'Your Kitchen is Your First Pharmacy', 'content' => 'Ayurveda teaches that food is your first and most powerful medicine. Common kitchen staples like turmeric, ginger, cumin, coriander, and fennel are revered healing agents.' ),
			array( 'heading' => 'Bathing & Bodily Signals', 'content' => 'You should not take a bath with extremely hot water even in winters to avoid your body losing sebum or the protective layer of fat.' ),
			array( 'heading' => 'Mindful Eating for a Strong Gut', 'content' => 'Eating only when you are extremely hungry and stop eating before filling the stomach completely will keep your digestive system strong and body free of obesity.' ),
		), $home_id );

		update_field( 'founder_enable', 1, $home_id );
		update_field( 'founder_label', '✦ Trusted Experience', $home_id );
		update_field( 'founder_name', 'Girish Mokeri', $home_id );
		update_field( 'founder_content', '<p>Girish Mokeri, the founder of Sparsha Ayurveda, is an esteemed Ayurvedic and Panchakarma Specialist from Kerala. With a doctorate in Naturopathy, he has accumulated years of working experience in India and several European countries.</p><p>Girish Mokeri\'s passion lies in the field of Ayurveda, and he is dedicated to conducting research and developing new methods of therapies and yoga. He firmly believes that <strong>Ayurveda is the best system of medicine</strong> due to its lack of side effects.</p><p>One of the greatest joys for Girish is witnessing the satisfaction of his guests and hearing about their positive experiences.</p>', $home_id );
		update_field( 'founder_quote', 'Nature has no side effects — that is Ayurveda\'s greatest gift.', $home_id );
		update_field( 'founder_tags', array(
			array( 'text' => 'Doctorate in Naturopathy' ),
			array( 'text' => 'Kerala Ayurveda Tradition' ),
			array( 'text' => '12+ Years in Europe' ),
		), $home_id );

		update_field( 'journal_enable', 1, $home_id );
		update_field( 'journal_label', 'Ayurvedic Journal', $home_id );
		update_field( 'journal_heading', 'Latest Articles', $home_id );
		update_field( 'journal_description', 'Hand-picked reads on Ayurveda, healing, and the art of living well.', $home_id );
		update_field( 'journal_posts', array(
			array( 'category' => 'Philosophy', 'read_time' => '4 min read', 'title' => 'The Concept of the Panchamahabhutas or the Five Great Elements', 'excerpt' => 'Understanding how Earth, Water, Fire, Air and Space form the basis of our constitution and guide treatment.' ),
			array( 'category' => 'Skin Health', 'read_time' => '6 min read', 'title' => '4 Ways to Manage Psoriasis Flare-Ups with Ayurveda', 'excerpt' => 'How Panchakarma and targeted herbal treatments address psoriasis from its root cause without steroids.' ),
			array( 'category' => 'Detox', 'read_time' => '5 min read', 'title' => 'Panchakarma (Detox) in Ayurveda', 'excerpt' => 'A deep dive into the five purification procedures and how they restore vitality, vigor and balance.' ),
		), $home_id );

		$log( "Home page seeded." );
	}

	// ═══════════════════════════════════════
	// CONTACT PAGE
	// ═══════════════════════════════════════
	$contact_id = $by_template( 'page-contact.php' );
	if ( $contact_id ) {
		$log( "Seeding Contact page (ID {$contact_id})..." );
		update_field( 'hero_heading', 'Get in Touch', $contact_id );
		update_field( 'form_heading', 'Book Your Appointment', $contact_id );
		update_field( 'form_description', "Fill in your details and we'll confirm your booking within 24 hours.", $contact_id );
		update_field( 'map_enable', 1, $contact_id );
		update_field( 'map_url', 'https://maps.google.com/?q=Csengery+Utca+64+Budapest', $contact_id );
		update_field( 'faq_enable', 1, $contact_id );
		update_field( 'faq_items', array(
			array( 'question' => 'Do I need to prepare anything before my first visit?', 'answer' => 'We recommend arriving 10 minutes early for your first visit. Wear comfortable, loose clothing and avoid eating a heavy meal 2 hours beforehand. Bring a list of any medications or supplements you are taking.' ),
			array( 'question' => 'How long is a typical treatment session?', 'answer' => 'Sessions range from 60 to 90 minutes for massage and relaxation treatments. Panchakarma detox programmes are typically conducted over multiple days (5–21 days). An initial consultation is 30–45 minutes.' ),
			array( 'question' => 'Are your treatments safe during pregnancy?', 'answer' => 'We offer specialised prenatal Ayurvedic therapies tailored for pregnancy. Please inform us during booking so our specialist can recommend the most suitable treatments.' ),
			array( 'question' => 'Do you offer gift vouchers?', 'answer' => 'Yes! Gift vouchers are available for all services and can be purchased by contacting us directly.' ),
			array( 'question' => 'What is your cancellation policy?', 'answer' => 'We kindly request at least 24 hours\' notice for cancellations or rescheduling. Late cancellations may incur a fee.' ),
		), $contact_id );
		$log( "Contact page seeded." );
	}

	// ═══════════════════════════════════════
	// ABOUT PAGE
	// ═══════════════════════════════════════
	$about_id = $by_template( 'page-about.php' );
	if ( $about_id ) {
		$log( "Seeding About page (ID {$about_id})..." );
		update_field( 'hero_heading', 'About', $about_id );
		update_field( 'hero_highlight', 'Sparsha', $about_id );
		update_field( 'philosophy_enable', 1, $about_id );
		update_field( 'philosophy_label', 'Our Philosophy', $about_id );
		update_field( 'philosophy_heading', 'Healing that Reaches Beyond the Physical', $about_id );
		update_field( 'philosophy_content', '<p><strong>Sparsha</strong> — a Sanskrit word meaning <em>"the profound healing touch"</em> — is the essence of everything we do.</p><p>Step into Sparsha Ayurveda and allow the ancient wisdom and artistry of Ayurveda to embrace you, nurturing your body, calming your mind, and uplifting your spirit.</p><p>Sparsha Ayurveda centre is in the heart of Budapest. Our centre embraces Indian traditions in a contemporary setting, using exclusively imported equipment from India.</p>', $about_id );
		update_field( 'stats_enable', 1, $about_id );
		update_field( 'stats_items', array(
			array( 'number' => '12', 'suffix' => '+', 'label' => 'Years serving Europe' ),
			array( 'number' => '100', 'suffix' => '%', 'label' => 'Authentic Ayurveda' ),
			array( 'number' => '6', 'suffix' => '+', 'label' => 'Core treatment areas' ),
			array( 'number' => '0', 'suffix' => '', 'label' => 'Side effects, naturally' ),
		), $about_id );
		update_field( 'founder_enable', 1, $about_id );
		update_field( 'founder_label', 'Meet the Founder', $about_id );
		update_field( 'founder_name', 'Dr. Girish Mokeri', $about_id );
		update_field( 'founder_content', '<p>A practitioner with a doctorate in Naturopathy and deep roots in Kerala\'s ancient healing traditions, Dr. Girish Mokeri founded Sparsha Ayurveda Centre with one mission: to bring the purest, most authentic Ayurvedic care to Europe.</p><p>His philosophy is simple — <strong>nature has no side effects.</strong></p>', $about_id );
		update_field( 'founder_quote', 'Nature has no side effects — that is Ayurveda\'s greatest gift.', $about_id );
		update_field( 'founder_tags', array(
			array( 'text' => 'Doctorate in Naturopathy' ),
			array( 'text' => 'Kerala Ayurveda Tradition' ),
			array( 'text' => '12+ Years in Europe' ),
		), $about_id );
		update_field( 'values_enable', 1, $about_id );
		update_field( 'values_items', array(
			array( 'title' => 'Authenticity', 'description' => 'Only genuine Kerala traditions — no shortcuts, no dilution of ancient wisdom.' ),
			array( 'title' => 'Compassion', 'description' => 'Every guest is treated as an individual — with care, patience, and deep respect.' ),
			array( 'title' => 'Consistency', 'description' => 'The same high standard of care — for every guest, every session, every time.' ),
			array( 'title' => 'Excellence', 'description' => 'Exclusively imported Indian equipment and premium herbal preparations — nothing less.' ),
		), $about_id );
		// Team
		update_field( 'team_enable', 1, $about_id );
		update_field( 'team_label', 'Meet Our Team', $about_id );
		update_field( 'team_heading', 'The People Behind Sparsha', $about_id );
		update_field( 'team_description', 'A dedicated team of Ayurveda specialists and Panchakarma therapists from Kerala — the birthplace of Ayurveda.', $about_id );
		if ( empty( get_field( 'team_members', $about_id ) ) || ( isset( $_GET['force'] ) && $_GET['force'] === '1' ) ) {
			$team_pool = get_posts( array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => 20,
				'fields'         => 'ids',
				'orderby'        => 'rand',
			) );
			$members = array(
				array(
					'name' => 'Girish Mokeri',
					'role' => 'Ayurveda Consultant',
					'bio'  => "<p>Girish Mokeri, the founder of Sparsha Ayurveda, is an esteemed Ayurvedic and Panchakarma Specialist from Kerala. With a doctorate in Naturopathy, he has accumulated years of working experience in India and several European countries.</p><p>Girish Mokeri's passion lies in the field of Ayurveda, and he is dedicated to conducting research and developing new methods of therapies and yoga. He firmly believes that Ayurveda is the best system of medicine due to its lack of side effects.</p><p>One of the greatest joys for Girish is witnessing the satisfaction of his guests and hearing about their positive experiences.</p>",
				),
				array(
					'name' => 'Jisha Biju',
					'role' => 'Ayurveda Therapist',
					'bio'  => "<p>Bringing a decade of dedicated expertise to our wellness team, Jisha Sebastian is a skilled Panchakarma therapist deeply rooted in the authentic traditions of Ayurveda. Hailing from Kerala, the very birthplace of this ancient healing science, her practice is infused with an innate understanding of its principles.</p><p>Jisha specializes in a wide range of Ayurvedic therapies and personalized treatments, administering them with a harmonious blend of profound knowledge, meticulous technique, and intuitive care. Her hands are not only trained but carry the legacy of Kerala's Ayurvedic heritage, ensuring each therapy is a truly restorative journey toward balance and holistic health for every client.</p>",
				),
				array(
					'name' => 'Aksa Thomas',
					'role' => 'Ayurveda Therapist',
					'bio'  => "<p>Aksa enriches our healing team with eight years of focused expertise as a dedicated Panchakarma therapist. Originating from Kerala, the sacred heartland of Ayurveda, her practice is naturally aligned with its time-honoured wisdom.</p><p>She specializes in the art of Ayurvedic massage and therapeutic treatments, executing each technique with precise, knowledgeable hands and a deeply calming presence. Aksa's approach masterfully blends traditional methods with attentive care, guiding each client toward profound relaxation and the restoration of vital energy.</p>",
				),
			);
			if ( $team_pool ) {
				shuffle( $team_pool );
				foreach ( $members as $mi => &$mem ) {
					$mem['image'] = (int) $team_pool[ $mi % count( $team_pool ) ];
				}
				unset( $mem );
			}
			update_field( 'team_members', $members, $about_id );
			$log( "Seeded " . count( $members ) . " team members." );
		}

		update_field( 'testimonials_enable', 1, $about_id );
		update_field( 'testimonials_items', array(
			array( 'text' => 'The Panchakarma treatment changed my life. I came in with chronic fatigue and joint pain — after three weeks I felt like a completely different person.', 'name' => 'Maria K.', 'location' => 'Budapest, Hungary' ),
			array( 'text' => 'The team at Sparsha is incredibly professional. The Abhyanga massage was deeply relaxing and the whole experience felt genuinely authentic.', 'name' => 'Thomas B.', 'location' => 'Vienna, Austria' ),
			array( 'text' => 'After years of skin issues I tried the Ayurvedic approach here. The herbal treatments made a visible difference within weeks.', 'name' => 'Anna R.', 'location' => 'Prague, Czech Republic' ),
		), $about_id );
		$log( "About page seeded." );
	}

	// ═══════════════════════════════════════
	// SERVICE PAGE
	// ═══════════════════════════════════════
	$service_id = $by_template( 'page-service.php' );
	if ( $service_id ) {
		$log( "Seeding Service page (ID {$service_id})..." );
		update_field( 'hero_tag', 'Detoxification & Rejuvenation', $service_id );
		update_field( 'hero_heading', 'Panchakarma', $service_id );
		update_field( 'hero_highlight', 'Detox Therapy', $service_id );
		update_field( 'overview_label', 'What is Panchakarma?', $service_id );
		update_field( 'overview_heading', 'The Crown Jewel of Ayurvedic Medicine', $service_id );
		update_field( 'overview_content', '<p>Panchakarma — literally "five actions" — is the cornerstone of Ayurvedic detoxification and rejuvenation. This comprehensive cleansing protocol systematically removes accumulated toxins (Ama) from deep within bodily tissues.</p><p>At Sparsha Ayurveda, each Panchakarma programme begins with a detailed consultation to assess your Prakriti (constitution) and Vikriti (current imbalance).</p>', $service_id );
		update_field( 'sidebar_duration', '5, 7, 14, or 21 days', $service_id );
		update_field( 'sidebar_who', 'All ages, tailored to your constitution', $service_id );
		update_field( 'sidebar_includes', 'Consultation, daily treatments & herbal preparations', $service_id );
		update_field( 'sidebar_pricing', 'Contact us for personalised quote', $service_id );
		update_field( 'steps_enable', 1, $service_id );
		update_field( 'steps_items', array(
			array( 'title' => 'Initial Consultation', 'description' => 'Dr. Girish Mokeri assesses your Prakriti (body constitution) and current health status through pulse diagnosis (Nadi Pariksha), physical examination, and detailed health history.' ),
			array( 'title' => 'Purvakarma – Preparation Phase', 'description' => 'The body is prepared through Snehana (internal and external oleation with medicated ghee and oils) and Swedana (herbal steam therapy).' ),
			array( 'title' => 'Pradhanakarma – Main Procedures', 'description' => 'The selected Panchakarma procedures are administered over the programme duration — typically 5 to 21 days.' ),
			array( 'title' => 'Paschatkarma – Post-Treatment Care', 'description' => 'Our specialists guide you through a personalised diet and lifestyle plan (Samsarjana Krama) to maintain the benefits of the treatment.' ),
		), $service_id );
		update_field( 'gallery_enable', 1, $service_id );
		$log( "Service page seeded." );
	}

	// ── Seed intro + therapies list for "Ayurveda Massage Therapy" treatment ──
	$massage = get_page_by_path( 'ayurveda-massage-therapy', OBJECT, 'treatment' );
	if ( $massage ) {
		$intro = '<p>Our Ayurveda Massage Therapy range includes the full spectrum of classical Ayurvedic treatments, each carefully matched to your individual constitution (<em>Prakriti</em>) and current imbalance (<em>Vikriti</em>).</p>';

		$shared_image = get_post_thumbnail_id( $massage->ID ); // parent treatment's featured image

		$therapies = array(
			array( 'title' => 'Abhyanga – Ayurvedic Herbal Oil Massage & Swedana', 'meta' => '70 min: 300 Lei | 100 min: 400 Lei', 'description' => 'A deeply nourishing full-body massage using warm, herb-infused oils and mindful, flowing strokes to ease joint pain, boost circulation, and calm the nervous system. The treatment is followed by Swedana—a medicated herbal steam bath that opens pores, flushes out toxins (ama), and aids digestion while keeping your head cool and comfortable.' ),
			array( 'title' => 'Shirodhara – Third Eye Therapy', 'meta' => '60 min: 350 Lei', 'description' => 'A profoundly relaxing ritual where a continuous, gentle stream of warm medicated oil is poured over the forehead (third eye). Designed to bring deep psychosomatic balance, Shirodhara quietens a busy mind, reduces stress and anxiety, enhances mental clarity, and promotes restorative sleep.' ),
			array( 'title' => 'Shirodhara & Abhyanga – Herbal Oil Massage', 'meta' => '120 min: 500 Lei', 'description' => 'The ultimate mind-body harmony therapy, combining the full-body warmth of an Abhyanga massage with the soothing, meditative flow of a Shirodhara forehead oil treatment. This dual approach offers deep relaxation, relieves physical tension, reduces stress, and activates your body\'s natural self-healing mechanisms.' ),
			array( 'title' => 'Elakizhi – Herbal Leaf Bag Massage', 'meta' => '90 min: 350 Lei', 'description' => 'An ancient therapeutic massage using warm bundles of medicinal leaves dipped in herbal oil to target deep-seated joint stiffness, muscle cramps, chronic back pain, and inflammatory conditions. It is completed with a Swedana herbal steam session to flush out excess toxins, boost circulation, and leave you entirely revitalized.' ),
			array( 'title' => 'Ayurvedic Pregnancy Massage', 'meta' => '60 min: 300 Lei', 'description' => 'Designed safely for expectant mothers in their second trimester onwards, this gentle Marma-based massage adapts to your specific stage of pregnancy. It strengthens muscles, relieves cramps, soothes itchy skin, reduces swelling, and minimizes stretch marks—all while offering emotional support and fostering a deeper bond between mother and baby.' ),
			array( 'title' => 'Udvarthanam – Weight Loss Therapy', 'meta' => '80 min: 350 Lei', 'description' => 'A dynamic detox treatment where specialized herbal powders are vigorously massaged over the body in upward strokes to stimulate lymphatic drainage, break down fatty deposits, and improve skin texture. Followed by a detoxifying Swedana herbal steam bath, this therapy boosts metabolism and supports overall body contouring.' ),
			array( 'title' => 'Pure Relaxation', 'meta' => '2.5 hours: 780 Lei', 'description' => 'A luxurious, top-to-bottom Ayurvedic journey designed for total mind-body rejuvenation. This all-inclusive package combines five signature treatments—Abhyanga, Shirodhara, Kizhi (herbal bags), Shiro Abhyanga (head massage), and a Swedana medicated steam bath—to release deep-seated stress, detoxify the system, and restore inner peace.' ),
			array( 'title' => 'Chakra Basti – Navel Basti', 'meta' => '40 min: 200 Lei', 'description' => 'A targeted abdominal therapy where warm medicated oil is retained inside a handcrafted ring of herbal dough placed around the navel (Nabhi). By stimulating this vital energy center, it balances the doshas, releases stored emotional tension, strengthens abdominal muscles, and aids digestive and reproductive health.' ),
			array( 'title' => 'Hridaya Basti – Heart Basti', 'meta' => '40 min: 200 Lei', 'description' => 'A deeply soothing chest treatment where warm, herbal-infused oil is held within a dough ring placed over the heart center. This comforting therapy supports heart and respiratory health, relieves chest tightness, and gently releases emotional stress, bringing a light, calm energy to your entire body.' ),
			array( 'title' => 'Greeva Basti – Neck Pain Treatment', 'meta' => '45 min: 200 Lei', 'description' => 'Warm medicated oil retained around the neck region. Alleviates neck pain, stiffness, and muscle tension. Beneficial for cervical spondylosis.' ),
			array( 'title' => 'Janu Basti – Knee Pain Treatment', 'meta' => '45 min: 250 Lei', 'description' => 'Warm medicated oil retained on the knee joint. Relieves knee pain, improves joint mobility, reduces inflammation.' ),
			array( 'title' => 'Kati Basti – Back Pain Treatment', 'meta' => '45 min: 200 Lei', 'description' => 'Warm medicated oil retained on the lower back. Effective for chronic back ache, sciatica, rheumatoid arthritis, lumbago, osteoarthritis.' ),
			array( 'title' => 'Prushtabhyanga – Back Massage', 'meta' => '35 min: 200 Lei', 'description' => 'Targets the lower back, buttocks, spine, waist, and shoulders. Focuses on Marma points to remove blockages and relax spinal nerves.' ),
			array( 'title' => 'Netra Tharpanam – Cleansing Eye Treatment', 'meta' => '40 min: 300 Lei', 'description' => 'A specialized rejuvenation treatment for the eyes where warm, medicated ghee or oil is carefully pooled over the eye area using dough rings. Ideal for digital eye strain, dry eyes, and dark circles, it strengthens the optic nerves, nourishes facial muscles, and clears vision.' ),
			array( 'title' => 'Shiro Abhyanga – Head & Neck Massage', 'meta' => '35 min: 150 Lei', 'description' => 'Warm herbal oils massaged into the scalp, neck, and shoulders. Promotes relaxation, reduces stress, improves sleep quality, removes fatigue, and helps cure hair loss.' ),
			array( 'title' => 'Nasya – Nasal Therapy', 'meta' => '30 min: 200 Lei', 'description' => 'A revitalizing treatment focused on clearing and purifying the nasal passages using medicated herbal drops and gentle head stimulation. Nasya offers immediate relief from sinus congestion, allergies, migraines, and headaches, leaving you feeling clear and able to breathe freely.' ),
			array( 'title' => 'Swedana – Medicated Steam Bath', 'meta' => '15 min: 150 Lei', 'description' => 'Sweating therapy encouraging removal of toxins. The individual sits in a medicinal wooden box while herbal steam opens pores and eliminates toxins.' ),
		);

		// Assign random images from the media library.
		$image_pool = get_posts( array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'orderby'        => 'rand',
			'suppress_filters' => true,
		) );
		if ( empty( $image_pool ) && $shared_image ) {
			$image_pool = array( (int) $shared_image );
		}
		$log( "Media pool size: " . count( $image_pool ) );
		if ( ! empty( $image_pool ) ) {
			shuffle( $image_pool );
			$pool_count = count( $image_pool );
			foreach ( $therapies as $idx => &$t ) {
				$t['image'] = (int) $image_pool[ $idx % $pool_count ];
			}
			unset( $t );
		} else {
			$log( "No images available in media library." );
		}

		update_field( 'treatment_content', $intro, $massage->ID );

		$force = isset( $_GET['force'] ) && $_GET['force'] === '1';
		$existing = get_field( 'treatment_therapies', $massage->ID );
		if ( $force || empty( $existing ) ) {
			update_field( 'treatment_therapies', $therapies, $massage->ID );
			$log( "Seeded " . count( $therapies ) . " therapies + intro for Ayurveda Massage Therapy." );
		} else {
			$log( "Ayurveda Massage Therapy already has " . count( $existing ) . " therapies — intro updated, therapies skipped. Add &force=1 to overwrite." );
		}
	} else {
		$log( "Ayurveda Massage Therapy treatment not found — skipped." );
	}

	// ── Seed RO translation: Masaj Ayurvedic ──
	$ro_slugs = array( 'masaj-ayurvedic', 'terapie-masaj-ayurvedic', 'masaj-ayurvedic-therapy' );
	$ro_post  = null;
	foreach ( $ro_slugs as $slug ) {
		$try = get_page_by_path( $slug, OBJECT, 'treatment' );
		if ( $try ) { $ro_post = $try; break; }
	}
	// Fallback: use Polylang to fetch translation if plugin active
	if ( ! $ro_post && $massage && function_exists( 'pll_get_post' ) ) {
		$ro_id = pll_get_post( $massage->ID, 'ro' );
		if ( $ro_id ) $ro_post = get_post( $ro_id );
	}
	if ( $ro_post ) {
		$intro_ro = '<p>Gama noastră de Masaj Ayurvedic include întregul spectru al tratamentelor ayurvedice clasice, fiecare potrivit cu grijă constituției dumneavoastră individuale (<em>Prakriti</em>) și dezechilibrului actual (<em>Vikriti</em>).</p>';

		$therapies_ro = array(
			array( 'title' => 'Abhyanga – Masaj cu Ulei pe bază de Plante & Swedana', 'meta' => '70 min: 300 Lei | 100 min: 400 Lei', 'description' => 'Un masaj hrănitor pentru întreg corpul, folosind uleiuri calde infuzate cu plante și mișcări atente și fluide, pentru a calma durerile articulare, a stimula circulația și a liniști sistemul nervos. Tratamentul este urmat de Swedana — o baie medicinală cu abur pe bază de plante care deschide porii, elimină toxinele (ama) și susține digestia, menținând totodată capul răcoros și confortabil.' ),
			array( 'title' => 'Shirodhara – Terapia celui de-al Treilea Ochi', 'meta' => '60 min: 350 Lei', 'description' => 'Un ritual profund relaxant în care un flux continuu și blând de ulei medicinal cald este turnat peste frunte (al treilea ochi). Conceput pentru a aduce echilibru psihosomatic profund, Shirodhara liniștește o minte agitată, reduce stresul și anxietatea, îmbunătățește claritatea mentală și promovează un somn odihnitor.' ),
			array( 'title' => 'Shirodhara & Abhyanga', 'meta' => '120 min: 500 Lei', 'description' => 'Terapia supremă de armonie minte-corp, care combină căldura masajului Abhyanga cu fluxul liniștitor și meditativ al tratamentului Shirodhara. Această abordare duală oferă relaxare profundă, ameliorează tensiunea fizică, reduce stresul și activează mecanismele naturale de auto-vindecare ale corpului.' ),
			array( 'title' => 'Elakizhi – Masaj cu Săculeți cu Frunze de Plante', 'meta' => '90 min: 350 Lei', 'description' => 'Un masaj terapeutic străvechi cu săculeți calzi din frunze medicinale înmuiate în ulei pe bază de plante, pentru a viza rigiditatea articulară profundă, crampele musculare, durerile cronice de spate și afecțiunile inflamatorii. Se încheie cu o sesiune de Swedana pentru a elimina toxinele, a stimula circulația și a vă lăsa complet revitalizat.' ),
			array( 'title' => 'Masaj Ayurvedic pentru Sarcină', 'meta' => '60 min: 300 Lei', 'description' => 'Conceput în siguranță pentru viitoarele mame începând cu al doilea trimestru, acest masaj Marma blând se adaptează stadiului specific al sarcinii. Întărește mușchii, ameliorează crampele, calmează pielea iritată, reduce umflăturile și minimizează vergeturile — oferind în același timp sprijin emoțional și favorizând o legătură mai profundă între mamă și copil.' ),
			array( 'title' => 'Udvarthanam – Terapie de Slăbire', 'meta' => '80 min: 350 Lei', 'description' => 'Un tratament de detoxifiere dinamic în care pudre pe bază de plante specializate sunt masate viguros pe corp cu mișcări ascendente, pentru a stimula drenajul limfatic, a descompune depozitele de grăsime și a îmbunătăți textura pielii. Urmat de o baie de abur cu plante Swedana, această terapie stimulează metabolismul și susține conturarea corpului.' ),
			array( 'title' => 'Relaxare Pură', 'meta' => '2,5 ore: 780 Lei', 'description' => 'O călătorie ayurvedică luxoasă, din cap până în picioare, concepută pentru rejuvenarea totală a minții și a corpului. Acest pachet complet combină cinci tratamente semnătură — Abhyanga, Shirodhara, Kizhi (săculeți cu plante), Shiro Abhyanga (masaj al capului) și baia de abur medicinală Swedana — pentru a elibera stresul acumulat, a detoxifia sistemul și a reda liniștea interioară.' ),
			array( 'title' => 'Chakra Basti – Basti la Ombilic', 'meta' => '40 min: 200 Lei', 'description' => 'O terapie abdominală țintită în care ulei medicinal cald este reținut într-un inel manual de aluat pe bază de plante plasat în jurul ombilicului (Nabhi). Prin stimularea acestui centru vital de energie, echilibrează doshele, eliberează tensiunea emoțională stocată, întărește mușchii abdominali și susține sănătatea digestivă și reproductivă.' ),
			array( 'title' => 'Hridaya Basti – Basti la Inimă', 'meta' => '40 min: 200 Lei', 'description' => 'Un tratament liniștitor pentru piept în care ulei cald pe bază de plante este reținut într-un inel de aluat plasat peste centrul inimii. Această terapie confortantă susține sănătatea inimii și a sistemului respirator, ameliorează senzația de apăsare toracică și eliberează blând tensiunea emoțională, aducând energie ușoară și calmă întregului corp.' ),
			array( 'title' => 'Greeva Basti – Tratament pentru Durerile de Gât', 'meta' => '45 min: 200 Lei', 'description' => 'Ulei medicinal cald reținut în jurul regiunii gâtului. Ameliorează durerile de gât, rigiditatea și tensiunea musculară. Benefic pentru spondiloza cervicală.' ),
			array( 'title' => 'Janu Basti – Tratament pentru Durerile de Genunchi', 'meta' => '45 min: 250 Lei', 'description' => 'Ulei medicinal cald reținut pe articulația genunchiului. Ameliorează durerea de genunchi, îmbunătățește mobilitatea articulară și reduce inflamația.' ),
			array( 'title' => 'Kati Basti – Tratament pentru Durerile de Spate', 'meta' => '45 min: 200 Lei', 'description' => 'Ulei medicinal cald reținut pe partea inferioară a spatelui. Eficient pentru durerile cronice de spate, sciatică, artrită reumatoidă, lumbago, osteoartrită.' ),
			array( 'title' => 'Prushtabhyanga – Masaj de Spate', 'meta' => '35 min: 200 Lei', 'description' => 'Vizează partea inferioară a spatelui, feselor, coloanei, taliei și umerilor. Se concentrează pe punctele Marma pentru a elimina blocajele și a relaxa nervii spinali.' ),
			array( 'title' => 'Netra Tharpanam – Tratament pentru Curățarea Ochilor', 'meta' => '40 min: 300 Lei', 'description' => 'Un tratament de rejuvenare specializat pentru ochi, în care ghee sau ulei medicinal cald este colectat cu grijă peste zona ochilor cu ajutorul unor inele de aluat. Ideal pentru oboseala oculară digitală, ochii uscați și cearcăne — întărește nervii optici, hrănește mușchii faciali și clarifică vederea.' ),
			array( 'title' => 'Shiro Abhyanga – Masaj de Cap & Gât', 'meta' => '35 min: 150 Lei', 'description' => 'Uleiuri calde pe bază de plante masate în scalp, gât și umeri. Promovează relaxarea, reduce stresul, îmbunătățește calitatea somnului, elimină oboseala și ajută la combaterea căderii părului.' ),
			array( 'title' => 'Nasya – Terapie Nazală', 'meta' => '30 min: 200 Lei', 'description' => 'Un tratament revitalizant axat pe curățarea și purificarea căilor nazale folosind picături medicinale pe bază de plante și stimulare blândă a capului. Nasya oferă alinare imediată pentru congestia sinusurilor, alergii, migrene și dureri de cap.' ),
			array( 'title' => 'Swedana – Baie de Abur Medicinală', 'meta' => '15 min: 150 Lei', 'description' => 'Terapie de transpirație care încurajează eliminarea toxinelor. Persoana stă într-o cabină din lemn medicinală, în timp ce aburul pe bază de plante deschide porii și elimină toxinele.' ),
		);

		// Build combined Full Content HTML from intro + all therapies
		$ro_html = $intro_ro;
		foreach ( $therapies_ro as $t ) {
			$ro_html .= "\n<h3>" . esc_html( $t['title'] ) . "</h3>\n";
			$ro_html .= '<p>' . esc_html( $t['description'] ) . '</p>' . "\n";
			if ( ! empty( $t['meta'] ) ) {
				$ro_html .= '<p><strong>' . esc_html( $t['meta'] ) . '</strong></p>' . "\n";
			}
		}
		update_field( 'treatment_content', $ro_html, $ro_post->ID );
		// Clear the Therapies List repeater on the RO post — RO uses Full Content only
		update_field( 'treatment_therapies', array(), $ro_post->ID );
		$log( "Moved RO therapies into Full Content (post ID {$ro_post->ID}); cleared Therapies List repeater." );
	} else {
		$log( "RO Masaj Ayurvedic post not found (checked slugs + Polylang). Create the translation first, then re-run." );
	}

	// ── Backfill therapy images across ALL treatment posts (all Polylang languages) ──
	$all_treatments = get_posts( array(
		'post_type'      => 'treatment',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'suppress_filters' => true, // include all languages
	) );
	$backfill_pool = get_posts( array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'post_status'    => 'inherit',
		'posts_per_page' => 200,
		'fields'         => 'ids',
		'orderby'        => 'rand',
		'suppress_filters' => true,
	) );
	if ( $all_treatments && $backfill_pool ) {
		$backfilled = 0;
		foreach ( $all_treatments as $tid ) {
			$rows = get_field( 'treatment_therapies', $tid );
			if ( empty( $rows ) ) continue;
			$changed = false;
			foreach ( $rows as $idx => &$r ) {
				$has_img = ! empty( $r['image'] ) && ( is_numeric( $r['image'] ) ? (int) $r['image'] > 0 : ( is_array( $r['image'] ) && ! empty( $r['image']['ID'] ) ) );
				if ( ! $has_img ) {
					$r['image'] = (int) $backfill_pool[ ( $tid + $idx ) % count( $backfill_pool ) ];
					$changed = true;
				}
			}
			unset( $r );
			if ( $changed ) {
				update_field( 'treatment_therapies', $rows, $tid );
				$backfilled++;
			}
		}
		$log( "Backfilled therapy images on {$backfilled} treatment post(s) (all languages)." );
	}

	// ═══════════════════════════════════════
	// AUTHENTIC PANCHAKARMA RETREAT PAGE
	// ═══════════════════════════════════════
	$pancha_id = $by_template( 'page-authentic-panchakarma-retreat.php' );
	if ( ! $pancha_id ) {
		$pancha_page = get_page_by_path( 'authentic-panchakarma-retreat' );
		if ( $pancha_page ) {
			$pancha_id = $pancha_page->ID;
		}
	}
	if ( $pancha_id ) {
		$log( "Seeding Authentic Panchakarma Retreat page (ID {$pancha_id})..." );

		update_field( 'pancha_hero_title', 'Authentic Panchakarma Retreat', $pancha_id );
		update_field( 'pancha_hero_slides', array(
			array(
				'title' => 'Authentic Panchakarma Retreat',
				'highlight' => 'Deep Cellular Detoxification',
				'highlight_color' => '#fcd34d',
				'subtitle' => 'Kerala Ayurveda · Voluntari, Romania',
			),
		), $pancha_id );
		update_field( 'pancha_intro_description', 'Sparsha is a Sanskrit word meaning "the profound healing touch"—the guiding philosophy behind every therapy we offer. Situated in Voluntari, Romania, Sparsha Ayurveda provides a peaceful wellness sanctuary where traditional Indian healing meets contemporary luxury. Using authentic equipment imported directly from Kerala, India, and guided by experienced specialists, our retreat offers a complete physical, mental, and spiritual reset.', $pancha_id );

		update_field( 'pancha_facilities_heading', 'Our Facilities & Accommodations', $pancha_id );
		update_field( 'pancha_facilities_subheading', 'Your retreat is designed to offer maximum comfort and complete peace of mind, allowing you to focus entirely on your healing:', $pancha_id );
		update_field( 'pancha_facilities_items', array(
			array(
				'title' => 'Private Apartment Accommodations',
				'description' => 'Relax in spacious, home-style master suites complete with high-end furnishings, private kitchenettes, living spaces, and 24/7 concierge support.',
				'note' => '',
			),
			array(
				'title' => 'Dosha-Balanced Wellness Cuisine',
				'description' => 'Savor organic, chef-prepared meals explicitly formulated around your unique body type (Prakriti) and detox requirements to ensure optimal nourishment without overburdening your digestive system.',
				'note' => 'To prepare for Panchakarma, it is important to start making dietary changes',
			),
			array(
				'title' => 'Infinity Pool & Relaxation Areas',
				'description' => 'Recharge between treatments with complimentary access to our outdoor infinity pool, lounge areas, and serene surroundings.',
				'note' => '',
			),
		), $pancha_id );

		update_field( 'pancha_journey_heading', 'The Panchakarma Healing Journey', $pancha_id );
		update_field( 'pancha_journey_question', 'Why Should One Consider Undergoing Ayurvedic Detox Therapy (Panchakarma)?', $pancha_id );
		update_field( 'pancha_journey_content', '<p>Ayurveda is an ancient Indian system of medicine that focuses on achieving balance and harmony in the body, mind, and spirit. It emphasizes the importance of a holistic approach to health and well-being, considering individual constitutions, lifestyle choices, and environmental factors.</p><p>Panchakarma is the method of purifying the impurities from the human body. The word panchakarma means Pancha (five) Karma (procedures/treatments), which means five treatments for the cleansing of our body.</p><p>We live in a world where we are in constant contact with harmful pollutants. They are present in the air we breathe, in the food we eat, and in the water we drink. Over time, these pollutants build up as toxins in our body and become the source of various health issues. Fortunately for us, our bodies have an in-built detoxification system to deal with these dangers. However, as we age, our bodies can’t get rid of the toxins as efficiently as they used to.</p><p>By making use of various detoxification methods, we can help our body perform its natural function effectively and expel the accumulated toxins to initiate the process of renewal and healing. By undergoing Panchakarma, an ayurvedic detoxification treatment, the body is cleansed of all toxic substances resulting in enhanced health, vitality, and vigor. Ayurveda Panchakarma treatment is a comprehensive detoxification and rejuvenation therapy that is an integral part of Ayurvedic healing.</p>', $pancha_id );
		update_field( 'pancha_dosha_title', 'Here\'s how the doshas are relevant in Panchakarma treatment', $pancha_id );
		update_field( 'pancha_dosha_steps', array(
			array(
				'title' => 'pre-treatment assessment',
				'description' => 'Before starting Panchakarma, a qualified Ayurvedic Specialist will assess your dosha imbalance to determine the most appropriate therapies for you.',
			),
			array(
				'title' => 'Customization',
				'description' => 'Panchakarma treatments are often customized to address individual imbalances in the doshas. By tailoring the therapies to your specific needs, Panchakarma aims to bring all three doshas into a state of balance.',
			),
			array(
				'title' => 'post-treatment care',
				'description' => 'After completing Panchakarma, it is important to follow specific diet and lifestyle recommendations to maintain the balance of the doshas. This helps to prolong the effects of the treatment and prevent future imbalances.',
			),
		), $pancha_id );
		update_field( 'pancha_journey_quote', 'Panchakarma is an ancient, deep-cellular detoxification program designed to clear accumulated metabolic toxins (Ama), unblock energy pathways (Srotas), and reset your body\'s inherent self-healing intelligence.', $pancha_id );

		update_field( 'pancha_program_heading', 'Our Program', $pancha_id );
		update_field( 'pancha_program_items', array(
			array(
				'title' => 'Comprehensive Consultation & Diagnosis',
				'description' => 'Your journey starts with an in-depth assessment involving pulse evaluation (Nadi Pariksha), tongue diagnosis, and physical examination to reveal root causes of health imbalances and identify your specific dosha profile (Vata, Pitta, Kapha).',
			),
			array(
				'title' => 'Daily Intensive Ayurvedic Therapies',
				'description' => 'Receive approximately two hours of daily specialized treatments, such as Abhyangam (medicated oil bodywork), Shirodhara (oil stream therapy for mental clarity), and custom herbal steam treatments.',
			),
			array(
				'title' => 'Supervised Gastrointestinal Cleansing (Virechana)',
				'description' => 'Experience controlled herbal purgation under expert medical supervision to remove deep-seated toxins from the liver, gallbladder, and digestive tract.',
			),
			array(
				'title' => 'Daily check-ins with your Panchakarma Consultant',
				'description' => 'Ongoing daily consultations track your internal detox progress, monitor your energy levels, and adjust your personalized herbal and treatment protocols in real time.',
			),
		), $pancha_id );

		update_field( 'pancha_benefits_heading', 'Transformational Health Benefits', $pancha_id );
		update_field( 'pancha_benefits_items', array(
			array(
				'title' => 'Removes toxic waste at a cellular level and boosts overall immunity',
				'description' => '',
			),
			array(
				'title' => 'Ignites metabolic fire (Agni), improves digestion, and supports natural weight balance',
				'description' => '',
			),
			array(
				'title' => 'Soothes the central nervous system, reducing anxiety, fatigue, and mental burnout',
				'description' => '',
			),
			array(
				'title' => 'Restores natural circadian rhythms for deeper, more restorative sleep',
				'description' => '',
			),
		), $pancha_id );

		update_field( 'pancha_pricing_heading', 'Flexible Retreat Options', $pancha_id );
		update_field( 'pancha_pricing_intro', 'We offer 7-Day, 10-Day, 21-Day and 14-Day retreat programs to accommodate your personal wellness goals and scheduling needs. All programs include daily intensive therapies, customized wellness dining, private accommodations, and continuous specialist care.', $pancha_id );
		update_field( 'pancha_pricing_note', 'Note: Package rates cover daily therapies, full-board dining, and accommodation. Prescribed herbal medicines for use during or post-retreat are charged separately based on your personalized treatment plan.', $pancha_id );
		update_field( 'pancha_pricing_packages', array(
			array(
				'title' => '7-Day Detox (Panchakarma Therapy)',
				'subtitle' => '',
				'rate' => '€100/day',
				'total' => '€700',
			),
			array(
				'title' => '10-Day Detox (Panchakarma Therapy)',
				'subtitle' => '',
				'rate' => '€100/day',
				'total' => '€1,000',
			),
			array(
				'title' => '14-Day Detox (Panchakarma Therapy)',
				'subtitle' => '',
				'rate' => '€100/day',
				'total' => '€1,400',
			),
			array(
				'title' => '21-Day Detox (Panchakarma Therapy)',
				'subtitle' => '',
				'rate' => '€100/day',
				'total' => '€2,100',
			),
		), $pancha_id );
		update_field( 'pancha_pricing_addons', array(
			array(
				'title' => 'Accommodation',
				'rate' => '€60/day',
				'note' => 'Calculated per night',
			),
			array(
				'title' => 'Food',
				'rate' => 'Full Board €60/day',
				'note' => 'Calculated per night',
			),
		), $pancha_id );

		update_field( 'pancha_cta_heading', 'Start Your Ayurveda Journey Today', $pancha_id );
		update_field( 'pancha_cta_vision', 'We envision a community where every individual is blessed with good health. At Sparsha, we advocate for the importance of leading a healthy life and provide natural remedies that promote a wholesome way of living.', $pancha_id );
		update_field( 'pancha_cta_desc', 'Take the first step towards a healthier and more balanced life with Ayurveda. Explore our website to learn more about our services, read informative articles on Ayurveda and schedule a consultation with our team of experts. For more information or to schedule a consultation, please contact us', $pancha_id );

		// ── Seeding Romanian Translation (Polylang) ──
		if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $pancha_id, 'en' );
			$ro_pancha_id = pll_get_post( $pancha_id, 'ro' );
			if ( ! $ro_pancha_id ) {
				$ro_page = get_page_by_path( 'retragere-autentica-de-panchakarma' );
				if ( $ro_page ) {
					$ro_pancha_id = $ro_page->ID;
				} else {
					$ro_pancha_id = wp_insert_post( array(
						'post_title'     => 'Retragere Autentică de Panchakarma',
						'post_name'      => 'retragere-autentica-de-panchakarma',
						'post_type'      => 'page',
						'post_status'    => 'publish',
						'page_template'  => 'page-authentic-panchakarma-retreat.php',
					) );
				}
				if ( $ro_pancha_id ) {
					pll_set_post_language( $ro_pancha_id, 'ro' );
					if ( function_exists( 'pll_save_post_translations' ) ) {
						pll_save_post_translations( array( 'en' => $pancha_id, 'ro' => $ro_pancha_id ) );
					}
				}
			}

			if ( $ro_pancha_id ) {
				update_post_meta( $ro_pancha_id, '_wp_page_template', 'page-authentic-panchakarma-retreat.php' );
				$log( "Seeding Romanian Authentic Panchakarma Retreat page (ID {$ro_pancha_id})..." );

				update_field( 'pancha_hero_title', 'Retragere Autentică de Panchakarma', $ro_pancha_id );
				update_field( 'pancha_hero_slides', array(
					array(
						'title' => 'Retragere Autentică de Panchakarma',
						'highlight' => 'Detoxifiere Celulară Profundă',
						'highlight_color' => '#fcd34d',
						'subtitle' => 'Kerala Ayurveda · Voluntari, România',
					),
				), $ro_pancha_id );
				update_field( 'pancha_intro_description', 'Sparsha este un cuvânt din limba sanscrită ce înseamnă „atingerea vindecătoare profundă” — filozofia care ghidează fiecare terapie pe care o oferim. Situat în Voluntari, România, Sparsha Ayurveda oferă un sanctuar liniștit de sănătate și relaxare, unde vindecarea tradițională indiană se întâlnește cu luxul contemporan. Folosind echipamente autentice importate direct din Kerala, India, și sub îndrumarea specialiștilor noștri experimentați, centrul nostru vă oferă o revitalizare fizică, mentală și spirituală completă.', $ro_pancha_id );

				update_field( 'pancha_facilities_heading', 'Facilitățile și Cazarea Noastră', $ro_pancha_id );
				update_field( 'pancha_facilities_subheading', 'Programul dumneavoastră este conceput pentru a vă oferi confort maxim și liniște sufletească deplină, permițându-vă să vă concentrați în totalitate pe procesul de vindecare:', $ro_pancha_id );
				update_field( 'pancha_facilities_items', array(
					array(
						'title' => 'Cazare în Apartamente Private',
						'description' => 'Relaxați-vă în apartamente spațioase, concepute în stil rezidențial, dotate cu mobilier de înaltă calitate, chicinetă privată, spații de relaxare și servicii de concierge 24/7.',
						'note' => '',
					),
					array(
						'title' => 'Gastronomie Echilibrată în Funcție de Dosha',
						'description' => 'Savurați preparate organice, concepute special de bucătari în funcție de tipologia dumneavoastră corporală unică (Prakriti) și cerințele de detoxifiere, asigurând o nutriție optimă fără a suprasolicita sistemul digestiv.',
						'note' => 'Pentru pregătirea procedurii Panchakarma, este esențial să începeți ajustarea alimentației.',
					),
					array(
						'title' => 'Piscină Infinity și Zone de Relaxare',
						'description' => 'Reîncărcați-vă energia între terapii având acces gratuit la piscina exterioră infinity, zonele de lounge și spațiile verzi din împrejurimi.',
						'note' => '',
					),
				), $ro_pancha_id );

				update_field( 'pancha_journey_heading', 'Călătoria de Vindecare prin Panchakarma', $ro_pancha_id );
				update_field( 'pancha_journey_question', 'De ce ar trebui să luați în considerare o terapie de detoxifiere ayurvedică (Panchakarma)?', $ro_pancha_id );
				update_field( 'pancha_journey_content', '<p>Ayurveda este un sistem medical tradițional indian axat pe obținerea echilibrului și armoniei între corp, minte și spirit. Aceasta accentuează importanța unei abordări holistice asupra sănătății și stării de bine, luând în considerare constituția individuală, stilul de viață și factorii de mediu.</p><p>Panchakarma este metoda de purificare a impurităților din organismul uman. Cuvântul Panchakarma provine din Pancha (cinci) și Karma (proceduri/tratamente), reprezentând cele cinci terapii esențiale pentru curățarea corpului.</p><p>Trăim într-o lume în care suntem în contact permanent cu poluanți dăunători. Aceștia sunt prezenți în aerul pe care îl respirăm, în hrana pe care o consumăm și în apa pe care o bem. Cu timpul, acești poluanți se acumulează sub formă de toxine în organism și devin sursa diverselor probleme de sănătate. Din fericire, corpul nostru dispune de un sistem intern de detoxifiere pentru a face față acestor pericole. Cu toate acestea, pe măsură ce înaintăm în vârstă, organismul nu mai poate elimina toxinele la fel de eficient.</p><p>Prin utilizarea diverselor metode de detoxifiere, ne putem ajuta corpul să își îndeplinească eficient funcția naturală și să elimine toxinele acumulate, inițiind procesul de reînnoire și vindecare. Prin terapia ayurvedică Panchakarma, organismul este curățat de substanțele toxice, rezultând o stare îmbunătățită de sănătate, vitalitate și energie. Tratamentul ayurvedic Panchakarma este o terapie complexă de detoxifiere și reîntinerire, fiind o componentă fundamentală a medicinei ayurvedice din plante.</p>', $ro_pancha_id );
				update_field( 'pancha_dosha_title', 'Rolul Dosha-urilor în Terapia Panchakarma', $ro_pancha_id );
				update_field( 'pancha_dosha_steps', array(
					array(
						'title' => 'Evaluarea Inițială',
						'description' => 'Înainte de a începe terapia Panchakarma, un specialist ayurvedic calificat va evalua dezechilibrul dosha-urilor dumneavoastră pentru a stabili cele mai potrivite terapii.',
					),
					array(
						'title' => 'Personalizarea',
						'description' => 'Tratamentele Panchakarma sunt adaptate pentru a aborda dezechilibrele specifice ale fiecărei dosha. Prin adaptarea terapiilor la nevoile dumneavoastră, Panchakarma își propune să aducă cele trei dosha-uri într-o stare de echilibru.',
					),
					array(
						'title' => 'Îngrijirea Post-Tratament',
						'description' => 'După finalizarea terapiei Panchakarma, este important să urmați recomandări specifice privind alimentația și stilul de viață pentru a menține echilibrul obținut. Acest lucru ajută la prelungirea efectelor tratamentului și previne viitoarele dezechilibre.',
					),
				), $ro_pancha_id );
				update_field( 'pancha_journey_quote', 'Panchakarma este un program străvechi de detoxifiere celulară profundă, conceput pentru a elimina toxinele metabolice acumulate (Ama), a debloca canalele energetice (Srotas) și a reactiva capacitatea naturală de autovindecare a organismului.', $ro_pancha_id );

				update_field( 'pancha_program_heading', 'Programul Nostru', $ro_pancha_id );
				update_field( 'pancha_program_items', array(
					array(
						'title' => 'Consultație Complexă și Diagnostic',
						'description' => 'Călătoria dumneavoastră începe cu o evaluare aprofundată ce include citirea pulsului (Nadi Pariksha), diagnosticul limbii și examenul fizic, pentru a identifica cauzele profunde ale dezechilibrelor și profilele dosha specifice (Vata, Pitta, Kapha).',
					),
					array(
						'title' => 'Terapii Ayurvedice Intensive Zilnice',
						'description' => 'Beneficiați de aproximativ două ore de tratamente specializate zilnice, cum ar fi Abhyangam (masaj corporal cu uleiuri medicinale), Shirodhara (terapia cu fir continuu de ulei pentru claritate mentală) și băi de abur cu plante medicinale.',
					),
					array(
						'title' => 'Purificație Gastrointestinală Monitorizată (Virechana)',
						'description' => 'Purgație din plante medicinale efectuită sub supraveghere medicală de specialitate, pentru eliminarea toxinelor profunde din ficat, vezica biliară și tractul digestiv.',
					),
					array(
						'title' => 'Evaluări Zilnice cu Consultantul Panchakarma',
						'description' => 'Consultațiile zilnice urmăresc progresul detoxifierii interne, monitorizează nivelul de energie și ajustează în timp real planul personalizat de plante și terapii.',
					),
				), $ro_pancha_id );

				update_field( 'pancha_benefits_heading', 'Beneficii Transformatoare Pentru Sănătate', $ro_pancha_id );
				update_field( 'pancha_benefits_items', array(
					array(
						'title' => 'Elimină reziduurile toxice la nivel celular și stimulează imunitatea generală.',
						'description' => '',
					),
					array(
						'title' => 'Aprinde focul metabolic (Agni), îmbunătățește digestia și susține echilibrul greutății corporale.',
						'description' => '',
					),
					array(
						'title' => 'Calmează sistemul nervos central, reducând anxietatea, oboseala și surmenajul mental.',
						'description' => '',
					),
					array(
						'title' => 'Restabilește ritmul circadian natural pentru un somn mai adânc și odihnitor.',
						'description' => '',
					),
				), $ro_pancha_id );

				update_field( 'pancha_pricing_heading', 'Programe Flexible de Sejur', $ro_pancha_id );
				update_field( 'pancha_pricing_intro', 'Vă oferim opțiuni de programe de 7, 10, 14 și 21 de zile, adaptate obiectivelor dumneavoastră de sănătate și disponibilității de timp. Toate programele includ terapii zilnice intensive, meniuri personalizate, cazare în apartament privat și îngrijire continuă din partea specialiștilor noștri.', $ro_pancha_id );
				update_field( 'pancha_pricing_note', 'Notă: Tarifele pachetelor acoperă terapiile zilnice, pensiunea completă și cazarea. Remedii din plante prescrise pentru utilizare în timpul sau după finalizarea programului se taxează separat, în funcție de planul personalizat.', $ro_pancha_id );
				update_field( 'pancha_pricing_packages', array(
					array(
						'title' => 'Detox 7-Zile (Terapie Panchakarma)',
						'subtitle' => '',
						'rate' => '€100 / zi',
						'total' => '€700',
					),
					array(
						'title' => 'Detox 10-Zile (Terapie Panchakarma)',
						'subtitle' => '',
						'rate' => '€100 / zi',
						'total' => '€1000',
					),
					array(
						'title' => 'Detox 14-Zile (Terapie Panchakarma)',
						'subtitle' => '',
						'rate' => '€100 / zi',
						'total' => '€1400',
					),
					array(
						'title' => 'Detox 21-Zile (Terapie Panchakarma)',
						'subtitle' => '',
						'rate' => '€100 / zi',
						'total' => '€2100',
					),
				), $ro_pancha_id );
				update_field( 'pancha_pricing_addons', array(
					array(
						'title' => 'Cazare',
						'rate' => '€60 / zi',
						'note' => 'Calculat per noapte',
					),
					array(
						'title' => 'Alimentație (Pensiune Completă)',
						'rate' => '€60 / zi',
						'note' => 'Calculat per noapte',
					),
				), $ro_pancha_id );

				update_field( 'pancha_cta_heading', 'Începeți Călătoria Ayurvedică Astăzi', $ro_pancha_id );
				update_field( 'pancha_cta_vision', 'Ne dorim o comunitate în care fiecare persoană se bucură de o sănătate deplină. La Sparsha, susținem importanța unui stil de viață sănătos și oferim remedii naturale ce promovează un mod de viață echilibrat.', $ro_pancha_id );
				update_field( 'pancha_cta_desc', 'Faceți primul pas către o viață mai sănătoasă și mai echilibrată prin Ayurveda. Explorați site-ul nostru pentru a afla mai multe despre serviciile noastre, citiți articole informative despre Ayurveda și programați o consultație cu echipa noastră de specialiști. Pentru mai multe informații sau pentru a programa o consultație, vă rugăm să ne contactați.', $ro_pancha_id );
			}
		}
	}

	$log( "ACF seeding complete for all pages." );
}
