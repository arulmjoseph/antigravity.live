<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

echo "=== SEEDING FACTS ACF LABELS ===\n";

update_field( 'facts_label', 'Ancient Ayurvedic Wisdom', 7 );
update_field( 'facts_heading', 'Interesting Facts', 7 );

update_field( 'facts_label', 'Înțelepciune Ayurvedică Străveche', 253 );
update_field( 'facts_heading', 'Fapte Interesante', 253 );

echo "Successfully set facts_label and facts_heading on home pages 7 (EN) and 253 (RO).\n";
