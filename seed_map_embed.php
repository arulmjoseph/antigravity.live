<?php
define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

$embed_url = 'https://maps.google.com/maps?q=Bd.+Pipera+nr.+1-VIII+D,+Voluntari,+Romania&t=&z=15&ie=UTF8&iwloc=&output=embed';

update_field( 'map_embed', $embed_url, 11 );
update_field( 'map_embed', $embed_url, 254 );

echo "Successfully updated map_embed ACF field for Contact pages ID 11 and 254.\n";
