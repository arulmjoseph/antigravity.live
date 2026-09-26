<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== UPDATING ALL TREATMENT ACF FIELDS ===\n";

$treatment_updates = array(
	// RO Post ID => array( badge, card_body )
	257 => array(
		'badge'     => 'Înrădăcinat în Înțelepciune. Creat Pentru Tine.',
		'card_body' => 'O consultație ayurvedică trece dincolo de simptome pentru a descoperi cauza profundă a problemelor dumneavoastră de sănătate. Prin înțelepciune străveche și observare, oferim un plan clar pentru restabilirea echilibrului (Doshas) și a vitalității, oferindu-vă puterea de a vă vindeca în mod natural.',
	),
	258 => array(
		'badge'     => 'Vindecare Străveche. Îngrijire Modernă.',
		'card_body' => 'O gamă completă de terapii de masaj ayurvedic autentic cu uleiuri calde infuzate cu plante. De la masajul corporal Abhyanga până la tratamente specifice — fiecare masaj hrănește corpul, elimină toxinele și restabilește echilibrul.',
	),
	259 => array(
		'badge'     => 'Echilibru. Armonie. Reînnoire.',
		'card_body' => 'Experimentați o liniște profundă cu terapia noastră de relaxare pură ayurvedică, unde tradițiile străvechi de vindecare armonizează energiile corpului, detoxifică organismul și liniștesc mintea. Conceput pentru a alunga stresul și anxietatea, acest amestec holistic de tratament restabilește echilibrul interior și vă sporește vitalitatea generală.',
	),
	260 => array(
		'badge'     => 'Detoxifiere. Restaurare. Revitalizare.',
		'card_body' => 'Programe terapeutice ayurvedice concepute pentru sănătatea pe termen lung, ameliorarea durerilor cronice, digestie sănătoasă și reîntinerirea completă a corpului.',
	),
	261 => array(
		'badge'     => 'Holistic. Natural. Eficient.',
		'card_body' => 'Terapii specializate concepute pentru a susține sănătatea femeilor în fiecare etapă a vieții, inclusiv sănătate ginecologică, îngrijire prenatală și postnatală.',
	),
	262 => array(
		'badge'     => 'Strălucire Din Interior.',
		'card_body' => 'Tratamente naturale ayurvedice pentru față, piele și scalp, formulate cu extracte botanice pure pentru a reda strălucirea naturală și o piele sănătoasă.',
	),
);

foreach ( $treatment_updates as $post_id => $data ) {
	if ( get_post( $post_id ) ) {
		update_field( 'treatment_badge', $data['badge'], $post_id );
		update_field( 'treatment_card_body', $data['card_body'], $post_id );
		echo "Updated ACF for Post ID {$post_id}: " . get_the_title( $post_id ) . "\n";
	}
}

echo "FINISHED UPDATING TREATMENT ACF FIELDS.\n";
