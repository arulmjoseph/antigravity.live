<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

$en_about_id = 8;
$ro_about_id = 287;

// English Team Members
$en_team = array(
	array(
		'image' => 250,
		'name'  => 'Girish Mokeri',
		'role'  => 'Ayurvedic Consultant',
		'bio'   => '<p>Girish Mokeri, the founder of Sparsha Ayurveda, is an esteemed Ayurvedic and Panchakarma Specialist from Kerala, India — the homeland of traditional medicine. With vast experience spanning India and Europe, Girish has dedicated his career to refining classical therapies and integrating holistic yoga practices into modern wellness.</p><p>Begin Your Journey Towards Balance and Health</p><p>We warmly invite you to discover your unique body constitution through our Personalized Dosha Assessment. Understanding your natural balance allows Girish and the Sparsha Ayurveda team to tailor treatments precisely for your body and mind.</p>',
	),
	array(
		'image' => 251,
		'name'  => 'Jisha Biju',
		'role'  => 'Ayurvedic Therapist',
		'bio'   => '<p>Originating from Kerala, the birthplace of Ayurveda, Jisha brings over 10 years of experience in Ayurvedic therapies. Specializing in authentic Panchakarma procedures, she combines traditional Kerala knowledge with meticulous technique and intuitive care, providing each guest with a profound experience of rebalancing and holistic healing.</p>',
	),
	array(
		'image' => 248,
		'name'  => 'Aksa Thomas',
		'role'  => 'Ayurvedic Therapist',
		'bio'   => '<p>Aksa enriches our healing team with eight years of focused expertise as a dedicated Panchakarma therapist. Originating from Kerala, her practice is naturally aligned with its time-honoured wisdom. She specializes in the art of Ayurvedic massage and therapeutic treatments with precise, knowledgeable hands.</p>',
	),
);

update_field( 'team_enable', 1, $en_about_id );
update_field( 'team_label', 'Meet Our Team', $en_about_id );
update_field( 'team_heading', 'The People Behind Sparsha', $en_about_id );
update_field( 'team_description', 'A dedicated team of Ayurvedic practitioners and Panchakarma therapists from Kerala — the sacred heartland of Ayurveda.', $en_about_id );
update_field( 'team_members', $en_team, $en_about_id );

// Romanian Team Members
$ro_team = array(
	array(
		'image' => 250,
		'name'  => 'Girish Mokeri',
		'role'  => 'Consultant Ayurveda',
		'bio'   => '<p>Girish Mokeri, fondatorul Sparsha Ayurveda, este un stimat specialist în Ayurveda și Panchakarma din Kerala, India — patria medicinei tradiționale. Cu o experiență vastă, ce se întinde în India și Europa, Girish și-a dedicat cariera rafinării terapiilor clasice și integrării practicilor holistice de yoga în starea de bine modernă.</p><p>Începeți Călătoria Dumneavoastră Spre Echilibru și Sănătate</p><p>Vă invităm cu drag să vă descoperiți tipologia corporală unică prin intermediul Evaluării Personalizate Dosha. Înțelegând echilibrul dumneavoastră natural, Girish și echipa Sparsha Ayurveda pot adapta tratamentele cu precizie pentru corpul și mintea dumneavoastră, ajutându-vă să obțineți o stare de sănătate armonioasă și de lungă durată.</p>',
	),
	array(
		'image' => 251,
		'name'  => 'Jisha Biju',
		'role'  => 'Terapeut Ayurveda',
		'bio'   => '<p>Originară din Kerala, locul de naștere al Ayurvedei, Jisha Sebastian aduce o experiență de peste 10 ani în terapia ayurvedică. Specializată în proceduri autentice Panchakarma, ea îmbină cunoștințele tradiționale din Kerala cu o tehnică meticuloasă și o îngrijire intuitivă, oferind fiecărui client o experiență profundă de reechilibrare și vindecare holistică.</p>',
	),
	array(
		'image' => 248,
		'name'  => 'Aksa Thomas',
		'role'  => 'Terapeut Ayurveda',
		'bio'   => '<p>Aksa îmbogățește echipa noastră de vindecare cu opt ani de experiență dedicată ca terapeut Panchakarma. Originară din Kerala, inima sacră a Ayurvedei, practica ei este aliniată firesc cu această înțelepciune străveche. Este specializată în arta masajului ayurvedic și a tratamentelor terapeutice, executate cu mâini precise și o prezență profund liniștitoare.</p>',
	),
);

update_field( 'team_enable', 1, $ro_about_id );
update_field( 'team_label', 'Cunoașteți Echipa Noastră', $ro_about_id );
update_field( 'team_heading', 'Oamenii din Spatele Sparsha', $ro_about_id );
update_field( 'team_description', 'O echipă dedicată de specialiști în Ayurveda și terapeuți Panchakarma din Kerala — patria consacrată a Ayurvedei.', $ro_about_id );
update_field( 'team_members', $ro_team, $ro_about_id );

echo "SUCCESS: Seeded Team members for both English (ID 8) and Romanian (ID 287) About pages.\n";
