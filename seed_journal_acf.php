<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== SEEDING JOURNAL SECTION ACF FIELDS ===\n";

update_field( 'journal_label', 'Jurnal Ayurvedic', 253 );
update_field( 'journal_heading', 'Ultimele Articole', 253 );
update_field( 'journal_description', 'Lecturi selectate despre Ayurveda, vindecare și arta de a trăi bine.', 253 );

echo "Successfully updated Romanian Journal section ACF fields on post ID 253.\n";
