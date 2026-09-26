<?php
/*
 * Template Name: Pricelist
 * Template Post Type: page
 */
get_header();
$uri          = get_template_directory_uri();
$hero_image   = get_field( 'hero_image' );
$hero_img_url = $hero_image ? $hero_image['url'] : $uri . '/assets/images/imgi_46_2.jpg';
$hero_img_alt = $hero_image ? $hero_image['alt'] : get_the_title();

$hero_tag       = sparsha_t( get_field( 'hero_tag' ) ?: 'Holistic Wellness Menu' );
$hero_heading   = sparsha_t( get_field( 'hero_heading' ) ?: 'Treatments &' );
$hero_highlight = sparsha_t( get_field( 'hero_highlight' ) ?: 'Price List' );
$hero_cta       = get_field( 'hero_cta' );

$intro_label    = sparsha_t( get_field( 'treatments_label' ) ?: 'Therapeutic Menu' );
$intro_heading  = sparsha_t( get_field( 'treatments_heading' ) ?: 'Ayurvedic Treatments & Pricing' );
$intro_desc     = sparsha_t( get_field( 'treatments_description' ) ?: 'Explore our classical Ayurvedic therapies, from targeted pain relief and skin purification to complete full-body rejuvenation rituals. Select any treatment below to view its details and pricing.' );

$side_image     = get_field( 'treatments_side_image' );
$side_img_url   = $side_image ? $side_image['url'] : $uri . '/assets/images/imgi_48_4.jpg';
$side_img_alt   = $side_image ? $side_image['alt'] : 'Ayurvedic Treatment Room';

$side_title     = get_field( 'side_title' ) ?: 'Authentic Healing Touch';
$side_text      = get_field( 'side_text' ) ?: 'Each treatment is customized with warm, herb-infused oils tailored to your body type (Dosha) for optimal restorative effects.';
?>

  <!-- Hero -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $hero_img_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
          <?php if ( $hero_tag ) : ?>
          <span class="inline-block bg-amber-500/20 text-amber-300 text-xs font-semibold px-3 py-1 rounded-full border border-amber-500/30 mb-3"><?php echo esc_html( $hero_tag ); ?></span>
          <?php endif; ?>
          <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( $hero_heading ); ?><?php if ( $hero_highlight ) : ?><br><em class="text-amber-300 not-italic"><?php echo esc_html( $hero_highlight ); ?></em><?php endif; ?></h1>
        </div>
        <a href="<?php echo esc_url( $hero_cta['url'] ?? home_url( '/contact' ) ); ?>" class="bg-amber-500 hover:bg-amber-400 text-bark font-semibold px-7 py-3.5 rounded-full transition-colors shadow-lg flex-shrink-0">
          <?php echo esc_html( $hero_cta['title'] ?? 'Book Appointment' ); ?>
        </a>
      </div>
    </div>
  </section>

  <main>

    <!-- Accordion section -->
    <section class="py-24 px-6 bg-cream">
      <div class="max-w-7xl mx-auto">

        <div class="text-center mb-16 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( $intro_label ); ?></p>
          <h2 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900"><?php echo esc_html( $intro_heading ); ?></h2>
          <div class="h-[1px] bg-forest-900/20 w-24 mx-auto mt-4 mb-5"></div>
          <p class="text-forest-600 max-w-2xl mx-auto leading-relaxed"><?php echo esc_html( $intro_desc ); ?></p>
        </div>

        <div class="grid lg:grid-cols-12 gap-12 items-start">

          <!-- Accordion (treatments grouped by parent) -->
          <div class="lg:col-span-7 space-y-8">
            <?php
            // Get top-level treatments
            $parents = get_posts( array(
              'post_type'      => 'treatment',
              'post_status'    => 'publish',
              'post_parent'    => 0,
              'numberposts'    => -1,
              'orderby'        => 'menu_order',
              'order'          => 'ASC',
            ) );

            $icons = array( '📋', '💆', '✨', '🌿', '🌸', '👁', '📍', '💚', '🌺', '🌱' );

            if ( $parents ) : foreach ( $parents as $pi => $parent ) :
              $children = get_posts( array(
                'post_type'   => 'treatment',
                'post_status' => 'publish',
                'post_parent' => $parent->ID,
                'numberposts' => -1,
                'orderby'     => 'menu_order',
                'order'       => 'ASC',
              ) );
              $items = $children ? $children : array( $parent );
              $icon  = $icons[ $pi % count( $icons ) ];
            ?>
            <div class="reveal">
              <h3 class="font-serif text-xl font-bold text-forest-900 mb-4 pb-2 border-b border-forest-100 flex items-center gap-2">
                <span><?php echo esc_html( $icon ); ?></span> <?php echo esc_html( $parent->post_title ); ?>
              </h3>
              <div class="space-y-3">
                <?php foreach ( $items as $item ) :
                  $duration = get_field( 'treatment_duration', $item->ID );
                  $price    = get_field( 'treatment_price', $item->ID );
                  $short    = get_field( 'treatment_short_description', $item->ID );
                ?>
                <details class="group border border-forest-100 rounded-2xl bg-cream/20 hover:bg-cream/40 p-4 transition-all">
                  <summary class="list-none flex justify-between items-center cursor-pointer select-none">
                    <div class="pr-4">
                      <span class="font-serif text-base font-bold text-forest-900 block group-open:text-amber-700 transition-colors"><?php echo esc_html( $item->post_title ); ?></span>
                    </div>
                    <div class="text-right shrink-0 flex items-center gap-3">
                      <?php if ( $duration ) : ?><span class="text-xs text-forest-500 font-medium whitespace-nowrap"><?php echo esc_html( $duration ); ?></span><?php endif; ?>
                      <?php if ( $price ) : ?><span class="font-bold text-forest-900 whitespace-nowrap"><?php echo esc_html( $price ); ?></span><?php endif; ?>
                      <i class="fa-solid fa-chevron-down w-4 h-4 text-forest-600 transform group-open:rotate-180 transition-transform"></i>
                    </div>
                  </summary>
                  <div class="mt-4 pt-3 border-t border-forest-100/50 text-sm text-forest-600 leading-relaxed space-y-3">
                    <?php if ( $short ) : ?>
                    <p><?php echo esc_html( $short ); ?></p>
                    <?php endif; ?>
                    <div class="pt-2"><a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="inline-flex text-xs font-bold text-amber-700 hover:text-amber-800 underline"><?php esc_html_e( 'View Full Details', 'sparsha-wp' ); ?> &rarr;</a></div>
                  </div>
                </details>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>

          <!-- Side Image -->
          <div class="lg:col-span-5 sticky top-24 space-y-6">
            <div class="reveal rounded-3xl overflow-hidden shadow-2xl border-4 border-white shadow-forest-900/10 aspect-[4/5] max-w-md mx-auto">
              <img src="<?php echo esc_url( $side_img_url ); ?>" alt="<?php echo esc_attr( $side_img_alt ); ?>" class="w-full h-full object-cover">
            </div>
            <div class="bg-forest-50 rounded-2xl p-5 border border-forest-100 text-center max-w-md mx-auto">
              <h4 class="font-serif font-bold text-forest-900 text-base mb-1"><?php echo esc_html( $side_title ); ?></h4>
              <p class="text-sm text-forest-600 leading-relaxed"><?php echo esc_html( $side_text ); ?></p>
            </div>
          </div>

        </div>

      </div>
    </section>

  </main>

<?php get_footer(); ?>
