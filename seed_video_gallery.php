<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

$en_vg_id = 135;
$ro_vg_id = 290;

$en_videos = array(
	array(
		'youtube_id' => 'MpjKyJEzNUQ',
		'title'      => 'Natural Fertility with Ayurveda · Budapest & Bucharest',
	),
	array(
		'youtube_id' => '4ix14Q4auNg',
		'title'      => 'Ayurveda Detox Therapy (Panchakarma)',
	),
	array(
		'youtube_id' => '7b3XoZh4cd0',
		'title'      => 'How to Manage Rheumatoid Arthritis & Inflammation Naturally',
	),
	array(
		'youtube_id' => 'D6QDDRhYgFQ',
		'title'      => 'Struggling with Weight? Try This Ayurvedic Detox',
	),
	array(
		'youtube_id' => 'EqdFfumyZ10',
		'title'      => 'Understanding Diabetes: Reversing the Root Cause with Ayurveda',
	),
	array(
		'youtube_id' => 'imfEHCIpWUo',
		'title'      => 'Ayurveda Panchakarma Treatment & Classical Healing',
	),
	array(
		'youtube_id' => 'wgQE4MTGjAw',
		'title'      => 'The Ayurvedic Secret to Radiant Skin & Deep Detox',
	),
	array(
		'youtube_id' => 'yoiMhvZRowY',
		'title'      => 'How to Relieve Sciatica Nerve Pain Naturally with Ayurveda',
	),
	array(
		'youtube_id' => 'zTyu8tEmYFk',
		'title'      => 'Ayurvedic Secrets to Fertility & Vitality',
	),
);

$ro_videos = array(
	array(
		'youtube_id' => 'MpjKyJEzNUQ',
		'title'      => 'Fertilitate Naturală prin Ayurveda · Budapesta și București',
	),
	array(
		'youtube_id' => '4ix14Q4auNg',
		'title'      => 'Terapie de Detoxifiere Ayurvedică (Panchakarma)',
	),
	array(
		'youtube_id' => '7b3XoZh4cd0',
		'title'      => 'Gestionarea Artritei Reumatoide și a Inflamației în Mod Natural',
	),
	array(
		'youtube_id' => 'D6QDDRhYgFQ',
		'title'      => 'Vă confruntați cu greutatea? Încercați acest Detox Ayurvedic',
	),
	array(
		'youtube_id' => 'EqdFfumyZ10',
		'title'      => 'Înțelegerea Diabetului: Tratarea Cauzei Profunde prin Ayurveda',
	),
	array(
		'youtube_id' => 'imfEHCIpWUo',
		'title'      => 'Tratament Ayurvedic Panchakarma și Vindecare Clasică',
	),
	array(
		'youtube_id' => 'wgQE4MTGjAw',
		'title'      => 'Secretul Ayurvedic pentru o Piele Strălucitoare și Detox Profund',
	),
	array(
		'youtube_id' => 'yoiMhvZRowY',
		'title'      => 'Cum să Ameliorați Durerile de Nerv Sciatic în Mod Natural',
	),
	array(
		'youtube_id' => 'zTyu8tEmYFk',
		'title'      => 'Secretele Ayurvedice pentru Fertilitate și Vitalitate',
	),
);

// English Video Gallery
update_field( 'gallery_heading', 'Video Gallery', $en_vg_id );
update_field( 'gallery_label', 'Watch & Learn', $en_vg_id );
update_field( 'gallery_subheading', 'Guest Stories & Ayurvedic Wisdom', $en_vg_id );
update_field( 'videos', $en_videos, $en_vg_id );
update_field( 'youtube_channel_url', 'https://www.youtube.com/channel/UC_hTPa9a8_gnHSOKD1JMh5g', $en_vg_id );

// Romanian Video Gallery
update_field( 'gallery_heading', 'Galerie Video', $ro_vg_id );
update_field( 'gallery_label', 'Vizionează și Învață', $ro_vg_id );
update_field( 'gallery_subheading', 'Povești ale Oaspeților și Înțelepciune Ayurvedică', $ro_vg_id );
update_field( 'videos', $ro_videos, $ro_vg_id );
update_field( 'youtube_channel_url', 'https://www.youtube.com/channel/UC_hTPa9a8_gnHSOKD1JMh5g', $ro_vg_id );

echo "SUCCESS: Seeded 9 YouTube channel videos for English (ID 135) and Romanian (ID 290) Video Gallery pages.\n";
