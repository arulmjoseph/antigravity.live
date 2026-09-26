<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

$en_home_id = 7;
$ro_home_id = 253;

$en_videos = array(
	array(
		'video_id' => 'MpjKyJEzNUQ',
		'title'    => 'Natural Fertility with Ayurveda',
	),
	array(
		'video_id' => '4ix14Q4auNg',
		'title'    => 'Ayurvedic Healing & Panchakarma',
	),
	array(
		'video_id' => '7b3XoZh4cd0',
		'title'    => 'Guest Experience & Recovery Journey',
	),
	array(
		'video_id' => 'D6QDDRhYgFQ',
		'title'    => 'Traditional Kerala Therapies & Consultation',
	),
	array(
		'video_id' => 'EqdFfumyZ10',
		'title'    => 'Holistic Detox & Mind Rejuvenation',
	),
	array(
		'video_id' => 'imfEHCIpWUo',
		'title'    => 'Ancient Wisdom for Modern Wellness',
	),
);

$ro_videos = array(
	array(
		'video_id' => 'MpjKyJEzNUQ',
		'title'    => 'Fertilitate Naturală prin Ayurveda',
	),
	array(
		'video_id' => '4ix14Q4auNg',
		'title'    => 'Vindecare Ayurvedică și Panchakarma',
	),
	array(
		'video_id' => '7b3XoZh4cd0',
		'title'    => 'Experiența Oaspeților și Călătoria de Recuperare',
	),
	array(
		'video_id' => 'D6QDDRhYgFQ',
		'title'    => 'Terapii Tradiționale din Kerala și Consultație',
	),
	array(
		'video_id' => 'EqdFfumyZ10',
		'title'    => 'Detoxifiere Holistică și Reîntinerirea Minții',
	),
	array(
		'video_id' => 'imfEHCIpWUo',
		'title'    => 'Înțelepciune Străveche pentru o Sănătate Modernă',
	),
);

// Update English Home Page
update_field( 'youtube_enable', 1, $en_home_id );
update_field( 'youtube_label', 'Real Experiences', $en_home_id );
update_field( 'youtube_heading', 'Our Delighted Guests Says', $en_home_id );
update_field( 'youtube_description', 'Hear from our guests about their transformative Ayurvedic journeys at Sparsha — straight from their hearts.', $en_home_id );
update_field( 'youtube_videos', $en_videos, $en_home_id );
update_field( 'youtube_channel_url', 'https://www.youtube.com/channel/UC_hTPa9a8_gnHSOKD1JMh5g', $en_home_id );

// Update Romanian Home Page
update_field( 'youtube_enable', 1, $ro_home_id );
update_field( 'youtube_label', 'Experiențe Reale', $ro_home_id );
update_field( 'youtube_heading', 'Ce Spun Oaspeții Noștri', $ro_home_id );
update_field( 'youtube_description', 'Aflați de la oaspeții noștri despre călătoriile lor transformatoare prin Ayurveda la Sparsha — direct din inimă.', $ro_home_id );
update_field( 'youtube_videos', $ro_videos, $ro_home_id );
update_field( 'youtube_channel_url', 'https://www.youtube.com/channel/UC_hTPa9a8_gnHSOKD1JMh5g', $ro_home_id );

echo "SUCCESS: Seeded 6 videos (3x2 grid) in ACF for English (ID 7) and Romanian (ID 253) Home pages.\n";
