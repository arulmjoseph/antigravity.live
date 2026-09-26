<?php
/**
 * Treatment Card — shared between archive-treatment.php and the home services section.
 * Must run inside the loop (the_post already called).
 * Args: [ 'index' => int ] for fallback image + badge rotation.
 */
$uri   = get_template_directory_uri();
$index = isset( $args['index'] ) ? (int) $args['index'] : 0;

$short    = get_field( 'treatment_short_description' ) ?: get_the_excerpt();
$children = get_posts( array(
	'post_type'   => 'treatment',
	'post_status' => 'publish',
	'post_parent' => get_the_ID(),
	'orderby'     => 'menu_order',
	'order'       => 'ASC',
	'numberposts' => -1,
) );

$fallback_images = array(
	'imgi_49_Detox-Your-Body-With-Ayurveda.jpg',
	'imgi_46_2.jpg',
	'imgi_47_3.jpg',
	'imgi_48_4.jpg',
);
$badges = array(
	'Rooted in Wisdom. Made for You.',
	'Ancient Healing. Modern Care.',
	'Balance. Harmony. Renewal.',
	'Detox. Restore. Revitalise.',
	'Holistic. Natural. Effective.',
	'Glow from Within.',
);
$badge_text = get_field( 'treatment_badge' ) ?: $badges[ $index % count( $badges ) ];
$fb_img     = $fallback_images[ $index % count( $fallback_images ) ];
?>
<article class="treatment-card reveal">

  <!-- Image -->
  <div class="treatment-card-img">
    <?php if ( has_post_thumbnail() ) : ?>
      <?php the_post_thumbnail( 'sparsha-card', array( 'alt' => get_the_title() ) ); ?>
    <?php else : ?>
      <img src="<?php echo esc_url( $uri . '/assets/images/' . $fb_img ); ?>" alt="<?php the_title_attribute(); ?>">
    <?php endif; ?>

    <!-- Badge -->
    <div class="treatment-card-badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2C12 2 6 8 6 14c0 3.3 2.7 6 6 6s6-2.7 6-6c0-6-6-12-6-12z"/><path d="M12 14c-2 0-4-1-5-3"/></svg>
      <span><?php echo esc_html( mb_strtoupper( sparsha_t( $badge_text ), 'UTF-8' ) ); ?></span>
    </div>

  </div>

  <!-- Content -->
  <div class="treatment-card-body">

    <!-- Leaf decorations (fern branch) -->
    <svg class="treatment-card-leaf-l" viewBox="0 0 200 320" fill="none" stroke="#285a14" stroke-width="1.4" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg">
      <path d="M100 320 C 110 240 90 160 100 80 C 105 50 100 30 100 10"/>
      <path d="M100 280 C 60 270 30 245 22 210 C 55 220 85 245 100 280Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 220 C 65 215 40 195 35 165 C 65 175 92 195 100 220Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 160 C 72 158 52 140 48 115 C 72 124 95 142 100 160Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 105 C 80 105 65 90 62 70 C 80 76 96 92 100 105Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 250 C 140 245 170 222 180 188 C 145 196 115 220 100 250Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 190 C 135 188 160 168 168 140 C 140 148 110 168 100 190Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 130 C 128 128 148 112 154 90 C 130 96 108 112 100 130Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 75 C 120 73 134 60 138 42 C 120 48 104 60 100 75Z" fill="#479228" fill-opacity="0.35"/>
    </svg>
    <svg class="treatment-card-leaf-r" viewBox="0 0 200 320" fill="none" stroke="#285a14" stroke-width="1.4" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg">
      <path d="M100 320 C 110 240 90 160 100 80 C 105 50 100 30 100 10"/>
      <path d="M100 280 C 60 270 30 245 22 210 C 55 220 85 245 100 280Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 220 C 65 215 40 195 35 165 C 65 175 92 195 100 220Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 160 C 72 158 52 140 48 115 C 72 124 95 142 100 160Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 105 C 80 105 65 90 62 70 C 80 76 96 92 100 105Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 250 C 140 245 170 222 180 188 C 145 196 115 220 100 250Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 190 C 135 188 160 168 168 140 C 140 148 110 168 100 190Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 130 C 128 128 148 112 154 90 C 130 96 108 112 100 130Z" fill="#479228" fill-opacity="0.35"/>
      <path d="M100 75 C 120 73 134 60 138 42 C 120 48 104 60 100 75Z" fill="#479228" fill-opacity="0.35"/>
    </svg>

    <!-- Title -->
    <h3 class="treatment-card-title"><?php the_title(); ?></h3>

    <!-- Divider -->
    <div class="treatment-card-divider">
      <div class="treatment-card-divider-line"></div>
      <svg width="16" height="14" viewBox="0 0 40 36" fill="none">
        <path d="M20 30 C20 30 8 22 8 14 C8 8 13 4 20 4 C27 4 32 8 32 14 C32 22 20 30 20 30Z" fill="#d97706" opacity="0.85"/>
        <path d="M20 30 C20 30 6 20 3 12 C1 6 5 2 10 3" stroke="#d97706" stroke-width="1.2" fill="none" opacity="0.6"/>
        <path d="M20 30 C20 30 34 20 37 12 C39 6 35 2 30 3" stroke="#d97706" stroke-width="1.2" fill="none" opacity="0.6"/>
      </svg>
      <div class="treatment-card-divider-line"></div>
    </div>

    <!-- Body: manual paragraph → short description → children list -->
    <?php $card_body = get_field( 'treatment_card_body' ); ?>
    <?php if ( $card_body ) : ?>
    <p class="treatment-card-body-text"><?php echo esc_html( $card_body ); ?></p>
    <?php elseif ( $short ) : ?>
    <p class="treatment-card-desc"><?php echo esc_html( wp_trim_words( $short, 22 ) ); ?></p>
    <?php elseif ( $children ) : ?>
    <ul class="treatment-card-children">
      <?php foreach ( $children as $child ) : ?>
      <li>
        <a href="<?php echo esc_url( get_permalink( $child ) ); ?>">
          <svg width="8" height="8" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="3"/></svg>
          <?php echo esc_html( $child->post_title ); ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <!-- CTA -->
    <a href="<?php the_permalink(); ?>" class="treatment-card-btn">
      <?php echo esc_html( sparsha_t( 'Know More' ) ); ?>
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>

  </div>
</article>
