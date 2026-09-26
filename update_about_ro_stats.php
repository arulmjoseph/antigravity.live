<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

$ro_about_id = 287; // Despre Noi

$stats = array(
	array(
		'number' => '12',
		'suffix' => '+',
		'label'  => 'Ani în slujba Europei',
	),
	array(
		'number' => '100',
		'suffix' => '%',
		'label'  => 'Ayurveda Autentică',
	),
	array(
		'number' => '6',
		'suffix' => '+',
		'label'  => 'Domenii principale de tratament',
	),
	array(
		'number' => '0',
		'suffix' => '',
		'label'  => 'Efecte secundare, în mod natural',
	),
);

update_field( 'stats_items', $stats, $ro_about_id );
update_field( 'philosophy_label', 'Filosofia Noastră', $ro_about_id );
update_field( 'philosophy_heading', 'Vindecare care trece dincolo de fizic', $ro_about_id );
update_field( 'founder_label', 'Cunoașteți Fondatorul', $ro_about_id );
update_field( 'founder_quote', 'Natura nu are efecte secundare — acesta este cel mai mare dar al Ayurvedei.', $ro_about_id );

echo "SUCCESS: Updated Romanian About Page (ID 287) stats and section fields.\n";
