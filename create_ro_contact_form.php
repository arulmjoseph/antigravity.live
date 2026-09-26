<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== CREATING ROMANIAN CONTACT FORM 7 FORM ===\n";

$title = 'Sparsha Rezervare Programare (RO)';

// Check if form already exists
$existing = get_page_by_title( $title, OBJECT, 'wpcf7_contact_form' );

$form_content = '<div class="cf7-row">
  <p class="cf7-field"><label>Prenume *</label>[text* first-name placeholder "Ana"]</p>
  <p class="cf7-field"><label>Nume de Familie *</label>[text* last-name placeholder "Popescu"]</p>
</div>
<div class="cf7-row">
  <p class="cf7-field"><label>Email *</label>[email* your-email placeholder "ana@example.com"]</p>
  <p class="cf7-field"><label>Telefon *</label>[tel* your-phone placeholder "+40 ..."]</p>
</div>
<p class="cf7-field"><label>Serviciu Solicitat *</label>[select* service include_blank "Consultanță Ayurvedică (45 min – 250 Lei)" "Abhyanga – Masaj cu Ulei pe bază de Plante & Swedana (70 min – 300 Lei)" "Abhyanga incl. Masaj al Capului, Feței & Gâtului & Swedana (100 min – 400 Lei)" "Shirodhara – Terapia celui de-al Treilea Ochi (60 min – 350 Lei)" "Shirodhara & Abhyanga – Masaj cu Ulei pe bază de Plante (120 min – 500 Lei)" "Elakizhi – Masaj cu Săculeți cu Frunze de Plante & Swedana (90 min – 350 Lei)" "Udvarthanam – Terapie de Slăbire (80 min – 350 Lei)" "Masaj Ayurvedic pentru Sarcină (60 min – 300 Lei)" "Mukhalepam – Tratament Facial Ayurvedic (60 min – 300 Lei)" "Relaxare Pură (2,5 ore – 780 Lei)" "Relaxare Pură cu Tratament Facial Ayurvedic (3,5 ore – 950 Lei)" "Terapie de Înfrumusețare Ayurvedică pentru Piele și Scalp Curat (3,5 ore – 950 Lei)" "Chakra Basti – Basti la Ombilic (40 min – 200 Lei)" "Hridaya Basti – Basti la Inimă (40 min – 200 Lei)" "Netra Tharpanam – Tratament pentru Curățarea Ochilor (40 min – 300 Lei)" "Nasya – Terapie Nazală (30 min – 200 Lei)" "Njavara Kizhi & Swedana (120 min – 500 Lei)" "Shiro Abhyanga – Masaj de Cap & Gât (35 min – 150 Lei)" "Prushtabhyanga – Masaj de Spate (35 min – 200 Lei)" "Kati Basti – Tratament pentru Durerile de Spate (45 min – 200 Lei)" "Greeva Basti – Tratament pentru Durerile de Gât (45 min – 200 Lei)" "Janu Basti – Tratament pentru Durerile de Genunchi (45 min – 250 Lei)" "Padabhyanga – Masaj Complet al Picioarelor (30 min – 200 Lei)" "Swedana – Baie de Abur Medicinală (15 min – 150 Lei)"]</p>
<div class="cf7-row">
  <p class="cf7-field"><label>Data Preferată</label>[date preferred-date]</p>
  <p class="cf7-field"><label>Ora Preferată</label>[select preferred-time include_blank "Dimineața (10:00 – 12:00)" "După-amiaza (12:00 – 16:00)" "Seara (16:00 – 20:00)"]</p>
</div>
<p class="cf7-field"><label>Probleme de Sănătate / Mesaj</label>[textarea your-message rows:4 placeholder "Vă rugăm să ne împărtășiți eventualele probleme de sănătate, alergii sau întrebări pe care le aveți…"]</p>
<p class="cf7-consent">[acceptance consent optional] Sunt de acord ca Sparsha Ayurveda Centre să stocheze și să proceseze datele mele pentru a răspunde acestei solicitări. Nu vom partaja informațiile dumneavoastră cu terțe părți. [/acceptance]</p>
<p>[submit "Trimiteți Solicitarea de Programare"]</p>';

$mail_meta = array(
	'active'             => true,
	'subject'            => '[_site_title] — Solicitare nouă de programare de la [first-name] [last-name]',
	'sender'             => '[_site_title] <wordpress@[_site_url]>',
	'recipient'          => 'info@sparshacare.com',
	'body'               => "Solicitare nouă de programare de la [first-name] [last-name]\n\nEmail: [your-email]\nTelefon: [your-phone]\nServiciu Solicitat: [service]\nData Preferată: [preferred-date]\nOra Preferată: [preferred-time]\n\nMesaj:\n[your-message]\n\n-- \nTrimis din formularul de contact Sparsha Ayurveda ([_site_title] [_site_url])",
	'additional_headers' => 'Reply-To: [your-email]',
	'attachments'        => '',
	'use_html'           => false,
	'exclude_blank'      => false,
);

$messages_meta = array(
	'mail_sent_ok'             => 'Vă mulțumim pentru mesaj. A fost trimis cu succes.',
	'mail_sent_ng'             => 'A apărut o eroare la trimiterea mesajului. Vă rugăm să încercați din nou mai târziu.',
	'validation_error'         => 'Unul sau mai multe câmpuri conțin o eroare. Vă rugăm să verificați și să încercați din nou.',
	'spam'                     => 'A apărut o eroare la trimiterea mesajului. Vă rugăm să încercați din nou mai târziu.',
	'accept_terms'             => 'Trebuie să acceptați termenii și condițiile înainte de a trimite mesajul.',
	'invalid_required'         => 'Vă rugăm să completați acest câmp.',
	'invalid_too_long'         => 'Acest câmp conține un text prea lung.',
	'invalid_too_short'        => 'Acest câmp conține un text prea scurt.',
	'upload_failed'            => 'A apărut o eroare necunoscută la încărcarea fișierului.',
	'upload_file_type_invalid' => 'Nu aveți permisiunea de a încărca fișiere de acest tip.',
	'upload_file_too_large'    => 'Fișierul încărcat este prea mare.',
	'upload_failed_php_error'  => 'A apărut o eroare la încărcarea fișierului.',
	'invalid_date'             => 'Vă rugăm să introduceți o dată în formatul AAAA-LL-ZZ.',
	'date_too_early'           => 'Această dată este prea devreme.',
	'date_too_late'            => 'Această dată este prea târziu.',
	'invalid_number'           => 'Vă rugăm să introduceți un număr.',
	'number_too_small'         => 'Acest număr este prea mic.',
	'number_too_large'         => 'Acest număr este prea mare.',
	'quiz_answer_not_correct'  => 'Răspunsul la quiz este incorect.',
	'captcha_not_match'        => 'Codul introdus este incorect.',
	'invalid_email'            => 'Vă rugăm să introduceți o adresă de email validă.',
	'invalid_url'              => 'Vă rugăm să introduceți un URL valid.',
	'invalid_tel'              => 'Vă rugăm să introduceți un număr de telefon valid.',
);

if ( $existing ) {
	$form_id = $existing->ID;
	echo "Found existing form (ID {$form_id}), updating...\n";
	wp_update_post( array(
		'ID'           => $form_id,
		'post_content' => $form_content,
	) );
} else {
	$form_id = wp_insert_post( array(
		'post_title'   => $title,
		'post_content' => $form_content,
		'post_type'    => 'wpcf7_contact_form',
		'post_status'  => 'publish',
	) );
	echo "Created new form (ID {$form_id})\n";
}

// Update CF7 meta
update_post_meta( $form_id, '_form', $form_content );
update_post_meta( $form_id, '_mail', $mail_meta );
update_post_meta( $form_id, '_messages', $messages_meta );
update_post_meta( $form_id, '_locale', 'ro_RO' );

$shortcode = sprintf( '[contact-form-7 id="%d" title="%s"]', $form_id, $title );
echo "Form shortcode: {$shortcode}\n";

// Update Romanian Contact Page (ID 254) ACF field
$ro_contact_page_id = 254;
if ( function_exists( 'update_field' ) ) {
	update_field( 'contact_form_shortcode', $shortcode, $ro_contact_page_id );
	echo "Updated contact_form_shortcode on Romanian Contact Page (ID {$ro_contact_page_id})\n";
} else {
	update_post_meta( $ro_contact_page_id, 'contact_form_shortcode', $shortcode );
	echo "Updated meta contact_form_shortcode on Romanian Contact Page (ID {$ro_contact_page_id})\n";
}

if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
echo "SUCCESS: Romanian Contact Form 7 form created and assigned.\n";
