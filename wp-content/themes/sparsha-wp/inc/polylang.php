<?php
/**
 * Polylang integration helpers.
 * Ensures internal links (home_url('/path'), nav menus, ACF link fields) stay in the current language.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Translate a theme UI string via Polylang if available, else via gettext.
 */
function sparsha_t( $string ) {
	if ( empty( $string ) || ! is_string( $string ) ) {
		return $string;
	}
	if ( function_exists( 'pll__' ) ) {
		$translated = pll__( $string );
		if ( $translated !== $string ) {
			return $translated;
		}
	}

	// Detect if current language is Romanian ('ro')
	$is_ro = false;
	if ( function_exists( 'pll_current_language' ) ) {
		$pll_lang = pll_current_language( 'slug' );
		if ( ! $pll_lang ) {
			$pll_lang = pll_current_language();
		}
		if ( $pll_lang === 'ro' ) {
			$is_ro = true;
		}
	}
	if ( ! $is_ro && isset( $_SERVER['REQUEST_URI'] ) && preg_match( '#/ro(/|$)#i', $_SERVER['REQUEST_URI'] ) ) {
		$is_ro = true;
	}
	if ( ! $is_ro && strpos( get_locale(), 'ro' ) === 0 ) {
		$is_ro = true;
	}

	// Fallback dictionary for common UI strings when language is Romanian
	if ( $is_ro ) {
		$string = str_replace(
			array( 'Bucharest, Romania', 'All rights reserved.', 'All rights reserved', 'Voluntari , Romania', 'Voluntari, Romania' ),
			array( 'București, România', 'Toate drepturile rezervate.', 'Toate drepturile rezervate', 'Voluntari, România', 'Voluntari, România' ),
			$string
		);
		$dict = array(
			// Contact page & Info
			'Visit Us'                            => 'Vizitați-ne',
			'Call Us'                             => 'Sunați-ne',
			'WhatsApp'                            => 'WhatsApp',
			'Email Us'                            => 'Trimiteți-ne un Email',
			'Opening Hours'                       => 'Program de Funcționare',
			'Mon – Fri'                           => 'Luni – Vineri',
			'Mon-Fri'                             => 'Luni – Vineri',
			'Saturday'                            => 'Sâmbătă',
			'Sunday'                              => 'Duminică',
			'By appointment'                      => 'Cu programare',
			'Follow Us'                           => 'Urmăriți-ne',
			'Contact Us'                          => 'Contactați-ne',
			'CONTACT US'                          => 'CONTACTAȚI-NE',
			'Get in'                              => 'Contactați',
			'Touch'                               => '-ne',
			'Get in Touch'                        => 'Contactați-ne',

			// Single Treatment Sidebar & Elements
			'Treatment Details'                   => 'Detalii Tratament',
			'Duration'                            => 'Durată',
			'Price'                               => 'Preț',
			'Pricing Options'                     => 'Opțiuni de Preț',
			'Book This Treatment'                 => 'Rezervați Acest Tratament',
			'Call'                                => 'Sunați',
			'All Treatments'                      => 'Toate Tratamentele',
			'Treatment Gallery'                   => 'Galerie Tratamente',
			'Know More'                           => 'Aflați Mai Multe',
			'Learn More'                          => 'Aflați Mai Multe',
			'View Full Details'                   => 'Vezi Detaliile Complete',
			'View All'                            => 'Vezi Toate',
			'View All Treatments'                 => 'Vezi Toate Tratamentele',
			'Years'                               => 'Ani',
			'YEARS'                               => 'ANI',
			'Years of Healing'                    => 'Ani de Vindecare',
			'Years in Europe'                     => 'Ani în Europa',
			'YEARS IN EUROPE'                     => 'ANI ÎN EUROPA',
			'Years serving Europe'                => 'Ani în slujba Europei',
			'Authentic Ayurveda'                  => 'Ayurveda Autentică',
			'Core treatment areas'                => 'Domenii principale de tratament',
			'Side effects, naturally'             => 'Efecte secundare, în mod natural',
			'Our Philosophy'                      => 'Filosofia Noastră',
			'Healing that Reaches Beyond the Physical' => 'Vindecare care trece dincolo de fizic',
			'Meet the Founder'                    => 'Cunoașteți Fondatorul',
			'Book a Consultation'                 => 'Programați o Consultație',
			'Book Consultation'                   => 'Programați o Consultație',
			'BOOK CONSULTATION'                   => 'PROGRAMAȚI O CONSULTAȚIE',
			'All Services'                        => 'Toate Serviciile',
			'ALL SERVICES'                        => 'TOATE SERVICIILE',
			'Contact Us / Schedule a Consultation' => 'Contactați-ne / Programați o Consultație',
			'Still have questions?'               => 'Mai aveți întrebări?',
			'STILL HAVE QUESTIONS?'               => 'MAI AVEȚI ÎNTREBĂRI?',
			'Our specialists are happy to help — reach out and we\'ll get back to you within 24 hours.' => 'Specialiștii noștri sunt bucuroși să vă ajute — contactați-ne și vă vom răspunde în 24 de ore.',
			'Common Questions'                    => 'Întrebări Comune',
			'COMMON QUESTIONS'                    => 'ÎNTREBĂRI COMUNE',
			'Everything You Need to Know'         => 'Tot ce Trebuie să Știți',
			'EVERYTHING YOU NEED TO KNOW'         => 'TOT CE TREBUIE SĂ ȘTIȚI',
			'FAQs coming soon. Add questions in the FAQ page editor.' => 'Întrebările frecvente vor fi adăugate în curând.',
			'Nature has no side effects — that is Ayurveda\'s greatest gift.' => 'Natura nu are efecte secundare — acesta este cel mai mare dar al Ayurvedei.',
			'Rooted in Kerala, India'             => 'Cu rădăcini în Kerala, India',
			'ROOTED IN KERALA, INDIA'             => 'CU RĂDĂCINI ÎN KERALA, INDIA',
			'min read'                            => 'min lectură',
			'Visit Our YouTube Channel'           => 'Vizitați Canalul Nostru de YouTube',
			'Watch more guest stories & Ayurvedic wisdom on our YouTube channel' => 'Vizionați mai multe povești ale oaspeților și înțelepciune ayurvedică pe canalul nostru de YouTube',
			'FACT'                                => 'FAPT',
			'Fact'                                => 'Fapt',
			'Read More'                           => 'Citește mai mult',
			'Uncategorized'                       => 'Necategorizat',
			'UNCATEGORIZED'                       => 'NECATEGORIZAT',
			'Rooted in Wisdom. Made for You.'     => 'Înrădăcinat în Înțelepciune. Creat Pentru Tine.',
			'ROOTED IN WISDOM. MADE FOR YOU.'     => 'ÎNRĂDĂCINAT ÎN ÎNȚELEPCIUNE. CREAT PENTRU TINE.',
			'Ancient Healing. Modern Care.'       => 'Vindecare Străveche. Îngrijire Modernă.',
			'ANCIENT HEALING. MODERN CARE.'       => 'VINDECARE STRĂVECHE. ÎNGRIJIRE MODERNĂ.',
			'Balance. Harmony. Renewal.'          => 'Echilibru. Armonie. Reînnoire.',
			'BALANCE. HARMONY. RENEWAL.'          => 'ECHILIBRU. ARMONIE. REÎNNOIRE.',
			'Detox. Restore. Revitalise.'         => 'Detoxifiere. Restaurare. Revitalizare.',
			'DETOX. RESTORE. REVITALISE.'         => 'DETOXIFIERE. RESTAURARE. REVITALIZARE.',
			'Holistic. Natural. Effective.'       => 'Holistic. Natural. Eficient.',
			'HOLISTIC. NATURAL. EFFECTIVE.'       => 'HOLISTIC. NATURAL. EFICIENT.',
			'Glow from Within.'                   => 'Strălucire Din Interior.',
			'GLOW FROM WITHIN.'                   => 'STRĂLUCIRE DIN INTERIOR.',
			'Ancient Ayurvedic Wisdom'            => 'Înțelepciune Ayurvedică Străveche',
			'ANCIENT AYURVEDIC WISDOM'            => 'ÎNȚELEPCIUNE AYURVEDICĂ STRĂVECHE',
			'Interesting Facts'                   => 'Fapte Interesante',
			'Ayurvedic Treatments'                => 'Tratamente Ayurvedice',
			'AYURVEDIC TREATMENTS'                => 'TRATAMENTE AYURVEDICE',
			'Our Treatments'                      => 'Tratamentele Noastre',
			'OUR TREATMENTS'                      => 'TRATAMENTELE NOASTRE',
			'Ayurvedic Treatments & Pricing'      => 'Tratamente Ayurvedice și Prețuri',
			'Therapeutic Menu'                    => 'Meniu Terapeutic',
			'Holistic Wellness Menu'              => 'Meniu Holistic de Stare de Bine',
			'Treatments &'                        => 'Tratamente și',
			'Explore Services'                    => 'Explorați Serviciile',
			'EXPLORE SERVICES'                    => 'EXPLORAȚI SERVICIILE',
			'Ayurvedic Journal'                   => 'Jurnal Ayurvedic',
			'AYURVEDIC JOURNAL'                   => 'JURNAL AYURVEDIC',
			'Latest Articles'                     => 'Ultimele Articole',
			'LATEST ARTICLES'                     => 'ULTIMELE ARTICOLE',
			'Hand-picked reads on Ayurveda, healing, and the art of living well.' => 'Lecturi selectate despre Ayurveda, vindecare și arta de a trăi bine.',
			'What We Stand For'                   => 'Ce Reprezentăm',
			'WHAT WE STAND FOR'                   => 'CE REPREZENTĂM',
			'Our Core Values'                     => 'Valorile Noastre Fundamentale',
			'We Accept:'                          => 'Acceptăm:',
			'We Accept'                           => 'Acceptăm',
			'WE ACCEPT'                           => 'ACCEPTĂM',

			// Header, Footer & CTA Section
			'Book Appointment'                    => 'Rezervați o Programare',
			'Book Your Appointment'               => 'Rezervați-vă Programarea',
			'Book Your Appointment Over the Phone'=> 'Rezervați o Programare Telefonic',
			'Contact Sparsha Ayurveda'            => 'Contactați Sparsha Ayurveda',
			'Ready to Begin Your Healing Journey?'=> 'Sunteți pregătit să începeți călătoria de vindecare?',
			'Our specialists will guide you — no prior knowledge of Ayurveda needed.' => 'Specialiștii noștri vă vor ghida — nu este necesară o cunoaștere prealabilă a Ayurveda.',
			'Gift Voucher'                        => 'Voucher Cadou',
			'Gift Vouchers'                       => 'Vouchere Cadou',
			'Services'                            => 'Servicii',
			'SERVICES'                            => 'SERVICII',
			'Explore'                             => 'Explorați',
			'EXPLORE'                             => 'EXPLORAȚI',
			'About Us'                            => 'Despre Noi',
			'Journal'                             => 'Jurnal',
			'Price List'                          => 'Lista de Prețuri',
			'FAQ'                                 => 'Întrebări Frecvente',
			'Privacy Policy'                      => 'Politica de Confidențialitate',
			'Terms of Use'                        => 'Termeni și Condiții',
			'Authentic Ayurvedic healing rooted in the traditions of Kerala, India — bringing natural wellness to the heart of Europe since 2012.' => 'Vindecare ayurvedică autentică înrădăcinată în tradițiile din Kerala, India — aducând starea de bine naturală în inima Europei din 2012.',
		);
		if ( isset( $dict[ $string ] ) ) {
			return $dict[ $string ];
		}
	} else {
		// Reverse dictionary for English when Romanian text was entered in Customizer
		$dict_en = array(
			'Vindecare ayurvedică autentică înrădăcinată în tradițiile din Kerala, India — aducând starea de bine naturală în inima Europei din 2012.' => 'Authentic Ayurvedic healing rooted in the traditions of Kerala, India — bringing natural wellness to the heart of Europe since 2012.',
			'Vizitați-ne'                            => 'Visit Us',
			'Sunați-ne'                             => 'Call Us',
			'Trimiteți-ne un Email'                  => 'Email Us',
			'Program de Funcționare'                 => 'Opening Hours',
			'Luni – Vineri'                          => 'Mon – Fri',
			'Sâmbătă'                               => 'Saturday',
			'Duminică'                              => 'Sunday',
			'Cu programare'                          => 'By appointment',
			'Urmăriți-ne'                           => 'Follow Us',
			'Contactați-ne'                         => 'Contact Us',
			'CONTACTAȚI-NE'                         => 'CONTACT US',
			'Rezervați o Programare'                 => 'Book Appointment',
			'Rezervați-vă Programarea'               => 'Book Your Appointment',
			'Voucher Cadou'                         => 'Gift Voucher',
			'Vouchere Cadou'                        => 'Gift Vouchers',
			'Servicii'                              => 'Services',
			'SERVICII'                              => 'SERVICES',
			'Explorați'                             => 'Explore',
			'EXPLORAȚI'                             => 'EXPLORAȚI',
			'Despre Noi'                            => 'About Us',
			'Jurnal'                                => 'Journal',
			'Lista de Prețuri'                      => 'Price List',
			'Întrebări Frecvente'                   => 'FAQ',
			'Politica de Confidențialitate'         => 'Privacy Policy',
			'Termeni și Condiții'                   => 'Terms of Use',
		);
		if ( isset( $dict_en[ $string ] ) ) {
			return $dict_en[ $string ];
		}
	}
	return __( $string, 'sparsha-wp' );
}

/**
 * Get a UI label: Customizer override → translated default.
 */
function sparsha_label( $key, $default = '' ) {
	$val = get_theme_mod( $key, $default );
	return sparsha_t( $val ?: $default );
}

/**
 * Auto-prefix the current language slug onto relative paths passed to home_url().
 *
 * Default Polylang doesn't rewrite arbitrary `home_url('/contact')` calls. This filter does:
 * if the current language is non-default (e.g. 'ro'), it converts
 *   home_url('/contact') → http://site/ro/contact
 *
 * Idempotent: skips URLs that already start with /{lang}/.
 */
add_filter( 'home_url', 'sparsha_localize_home_url', 99, 2 );
function sparsha_localize_home_url( $url, $path ) {
	static $reentry = false;
	if ( $reentry ) return $url;                                       // prevent recursion
	if ( is_admin() || ! function_exists( 'pll_current_language' ) ) return $url;
	$lang = pll_current_language( 'slug' );
	if ( ! $lang ) return $url;

	// Default language with hide_default = no prefix
	$default = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : 'en';
	$opts    = get_option( 'polylang', array() );
	$hide_default = ! empty( $opts['hide_default'] );
	if ( $lang === $default && $hide_default ) return $url;
	if ( $lang === $default ) return $url; // EN is default and not hidden → URL is fine as /

	// Only rewrite when a path was passed (e.g. home_url('/contact'))
	if ( $path === '' || $path === '/' ) return $url;

	// Get the raw site URL (without any Polylang filtering)
	$reentry = true;
	$site_root = untrailingslashit( get_option( 'home' ) );
	$reentry = false;
	if ( ! $site_root ) return $url;

	$clean_path = '/' . ltrim( (string) $path, '/' );

	// If the path already starts with /{lang}/ skip
	if ( preg_match( '#^/' . preg_quote( $lang, '#' ) . '(/|$)#', $clean_path ) ) {
		return $site_root . $clean_path;
	}

	return $site_root . '/' . $lang . $clean_path;
}

/**
 * When PLL is active, register strings used in the theme so they appear in
 * wp-admin → Languages → Strings translations.
 * Editors can then enter Romanian translations for UI labels.
 */
add_action( 'init', 'sparsha_register_pll_strings' );
function sparsha_register_pll_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) return;
	$strings = array(
		'Book Appointment'             => 'Header / CTA button',
		'Menu'                         => 'Mobile menu button',
		'Home'                         => 'Breadcrumbs',
		'Read More'                    => 'Buttons',
		'Know More'                    => 'Treatment card',
		'View Full Details'            => 'Pricelist accordion',
		'Send Appointment Request'     => 'Contact form',
		'Privacy Policy'               => 'Footer',
		'Terms of Use'                 => 'Footer',
		'Ready to Begin Your Healing Journey?' => 'Pre-footer CTA',
		'Gift Voucher'                 => 'Pre-footer button',
		'Treatment Details'            => 'Single treatment sidebar',
		'Duration'                     => 'Single treatment sidebar',
		'Price'                        => 'Single treatment sidebar',
		'Pricing Options'              => 'Single treatment content',
		'Book This Treatment'          => 'Single treatment CTA',
		'Call'                         => 'Single treatment CTA',
		'All Treatments'               => 'Single treatment back link',
		'Treatments'                   => 'Breadcrumbs / archive',
		'Treatment Gallery'            => 'Single treatment gallery',
		'Learn More'                   => 'Therapies list CTA',
		'Journal'                      => 'Breadcrumbs / blog',
		'min read'                     => 'Post meta',
		'Topics'                       => 'Post tags',
		'Visit Us'                     => 'Contact page & footer',
		'Call Us'                      => 'Contact page & footer',
		'WhatsApp'                     => 'Contact page',
		'Email Us'                     => 'Contact page & footer',
		'Opening Hours'                => 'Contact page',
		'Mon – Fri'                    => 'Contact page',
		'Saturday'                     => 'Contact page',
		'Sunday'                       => 'Contact page',
		'By appointment'               => 'Contact page',
		'Follow Us'                    => 'Contact page',
		'Get in'                       => 'Contact page hero',
		'Touch'                        => 'Contact page hero',
		'Get in Touch'                 => 'Contact page hero',
		'Book Your Appointment'        => 'Contact page form heading',
		'Book Your Appointment Over the Phone' => 'Contact page phone heading',
		'Contact Us'                   => 'Footer title',
		'Explore'                      => 'Footer title',
		'Authentic Ayurvedic healing rooted in the traditions of Kerala, India — bringing natural wellness to the heart of Europe since 2012.' => 'Footer brand description',
		'Bd. Pipera nr. 1-VIII D, Voluntari, Romania' => 'Footer address',
		'H-1067 Budapest, Csengery Utca 64, Hungary'  => 'Footer address default',
		'Years'                        => 'Founder badge label',
		'Years in Europe'              => 'About badge label',
		'Rooted in Kerala, India'      => 'About origin tag',
		'View All'                     => 'Section button label',
		'View All Treatments'          => 'Section button label',
		'Visit Our YouTube Channel'    => 'YouTube section button',
		'Watch more guest stories & Ayurvedic wisdom on our YouTube channel' => 'YouTube section subtitle',
		'Rooted in Wisdom. Made for You.' => 'Treatment card badge',
		'Ancient Healing. Modern Care.'   => 'Treatment card badge',
		'Balance. Harmony. Renewal.'      => 'Treatment card badge',
		'Detox. Restore. Revitalise.'     => 'Treatment card badge',
		'Holistic. Natural. Effective.'   => 'Treatment card badge',
		'Glow from Within.'               => 'Treatment card badge',
		'Ancient Ayurvedic Wisdom'        => 'Facts section subtitle',
		'Interesting Facts'               => 'Facts section heading',
		'Ayurvedic Treatments'            => 'Treatment archive title',
		'Our Treatments'                  => 'Treatment archive subtitle',
		'Ayurvedic Treatments & Pricing'  => 'Price list heading',
		'Therapeutic Menu'                => 'Price list subtitle',
		'Holistic Wellness Menu'          => 'Price list hero tag',
		'Explore Services'                => 'Hero slider button',
		'Ayurvedic Journal'               => 'Journal section subtitle',
		'Latest Articles'                 => 'Journal section heading',
		'Hand-picked reads on Ayurveda, healing, and the art of living well.' => 'Journal section description',
		'What We Stand For'               => 'About values subtitle',
		'Our Core Values'                 => 'About values heading',
		'We Accept:'                      => 'Footer payment cards label',
	);
	foreach ( $strings as $str => $context ) {
		pll_register_string( $str, $str, 'sparsha-wp ' . $context );
	}
}
