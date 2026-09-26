<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== SYNCING POLYLANG STRING TRANSLATIONS ===\n";

if ( ! function_exists( 'PLL' ) ) {
	echo "Polylang is not active.\n";
	exit;
}

$dict_ro = array(
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
	'Book Your Appointment'               => 'Rezervați-vă Programarea',
	'Book Your Appointment Over the Phone'=> 'Rezervați o Programare Telefonic',

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
	'Bd. Pipera nr. 1-VIII D, Voluntari, Romania' => 'Bd. Pipera nr. 1-VIII D, Voluntari, Romania',
	'H-1067 Budapest, Csengery Utca 64, Hungary'  => 'Bd. Pipera nr. 1-VIII D, Voluntari, Romania',
);

$ro_lang = PLL()->model->get_language( 'ro' );
$en_lang = PLL()->model->get_language( 'en' );

if ( $ro_lang ) {
	$mo_ro = new PLL_MO();
	$mo_ro->import_from_db( $ro_lang );

	foreach ( $dict_ro as $original => $translation ) {
		$mo_ro->add_entry( $mo_ro->make_entry( $original, $translation ) );
	}

	$mo_ro->export_to_db( $ro_lang );
	echo "Exported Romanian translations to term_id " . $ro_lang->term_id . "\n";
}

if ( $en_lang ) {
	$mo_en = new PLL_MO();
	$mo_en->import_from_db( $en_lang );

	foreach ( $dict_ro as $original => $translation ) {
		$mo_en->add_entry( $mo_en->make_entry( $original, $original ) );
	}

	$mo_en->export_to_db( $en_lang );
	echo "Exported English translations to term_id " . $en_lang->term_id . "\n";
}

if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
echo "SUCCESS: Polylang string translations updated for EN and RO.\n";
