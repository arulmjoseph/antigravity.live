<?php
/*
 * Template Name: About
 * Template Post Type: page
 */
get_header();
$uri = get_template_directory_uri();

$hero_image     = get_field( 'hero_image' );
$hero_img_url   = $hero_image ? $hero_image['url'] : $uri . '/assets/images/imgi_47_3.jpg';
$hero_img_alt   = $hero_image ? $hero_image['alt'] : 'Shirodhara treatment';
$hero_heading   = get_field( 'hero_heading' ) ?: 'About';
$hero_highlight = get_field( 'hero_highlight' ) ?: 'Sparsha';
?>

  <!-- Hero -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $hero_img_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( $hero_heading ); ?> <em class="text-amber-300 not-italic"><?php echo esc_html( $hero_highlight ); ?></em></h1>
    </div>
  </section>

  <main>

    <?php if ( get_field( 'philosophy_enable' ) !== false ? get_field( 'philosophy_enable' ) : true ) :
      $phil_image   = get_field( 'philosophy_image' );
      $phil_img_url = $phil_image ? $phil_image['url'] : $uri . '/assets/images/imgi_46_2.jpg';
      $phil_img_alt = $phil_image ? $phil_image['alt'] : 'Authentic treatment';
    ?>
    <!-- Philosophy -->
    <section class="py-20 px-6 bg-cream">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
        <div class="reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-4"><?php echo esc_html( get_field( 'philosophy_label' ) ?: 'Our Philosophy' ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 leading-tight mb-6"><?php echo esc_html( get_field( 'philosophy_heading' ) ?: 'Healing that Reaches Beyond the Physical' ); ?></h2>
          <div class="text-forest-600 leading-relaxed space-y-5">
            <?php echo wp_kses_post( get_field( 'philosophy_content' ) ?: '<p><strong>Sparsha</strong> — a Sanskrit word meaning <em>"the profound healing touch"</em> — is the essence of everything we do.</p>' ); ?>
          </div>
        </div>
        <div class="reveal relative">
          <div class="rounded-3xl overflow-hidden aspect-[4/3] shadow-xl shadow-forest-900/15">
            <img src="<?php echo esc_url( $phil_img_url ); ?>" alt="<?php echo esc_attr( $phil_img_alt ); ?>" class="w-full h-full object-cover">
          </div>
          <div class="absolute -bottom-5 -right-4 bg-amber-500 text-bark rounded-2xl px-6 py-4 shadow-xl hidden sm:block">
            <p class="font-serif font-bold text-2xl leading-none">12+</p>
            <p class="text-xs font-semibold mt-0.5"><?php echo esc_html( sparsha_t( 'Years in Europe' ) ); ?></p>
          </div>
          <div class="absolute top-6 -left-4 bg-forest-800 text-amber-200 rounded-xl px-4 py-3 shadow-lg text-xs font-medium hidden sm:block">🌿 <?php echo esc_html( sparsha_t( 'Rooted in Kerala, India' ) ); ?></div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php
    $stats_enabled = get_field( 'stats_enable' ) !== false ? get_field( 'stats_enable' ) : true;
    $stats_items   = get_field( 'stats_items' );
    if ( $stats_enabled && $stats_items ) :
    ?>
    <!-- Stats -->
    <section class="bg-forest-900 py-14 px-6">
      <div class="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
        <?php foreach ( $stats_items as $stat ) : ?>
        <div class="reveal">
          <p class="font-serif text-5xl font-bold text-amber-400 mb-1"><?php echo esc_html( $stat['number'] ); ?><?php if ( ! empty( $stat['suffix'] ) ) : ?><span class="text-3xl"><?php echo esc_html( $stat['suffix'] ); ?></span><?php endif; ?></p>
          <p class="text-forest-300 text-sm"><?php echo esc_html( sparsha_t( $stat['label'] ) ); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ( get_field( 'founder_enable' ) !== false ? get_field( 'founder_enable' ) : true ) :
      $founder_name    = get_field( 'founder_name' ) ?: 'Dr. Girish Mokeri';
      $founder_quote   = get_field( 'founder_quote' ) ?: 'Nature has no side effects — that is Ayurveda\'s greatest gift.';
      $founder_content = get_field( 'founder_content' );
      $founder_tags    = get_field( 'founder_tags' );
      $founder_image   = get_field( 'founder_image' );
      $founder_img_url = is_array( $founder_image ) ? ( $founder_image['sizes']['medium_large'] ?? $founder_image['url'] ) : '';
    ?>
    <!-- Founder -->
    <section class="py-24 px-6 bg-white">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
        <div class="reveal relative order-2 lg:order-1">
          <div class="rounded-3xl bg-forest-100 aspect-[3/4] max-w-sm mx-auto overflow-hidden shadow-xl shadow-forest-900/10 relative">
            <?php if ( $founder_img_url ) : ?>
            <img src="<?php echo esc_url( $founder_img_url ); ?>" alt="<?php echo esc_attr( $founder_name ); ?>" class="w-full h-full object-cover" loading="lazy">
            <?php else : ?>
            <div class="w-full h-full flex items-center justify-center">
              <i class="fa-solid fa-user text-8xl text-forest-300"></i>
            </div>
            <?php endif; ?>
          </div>
          <div class="absolute -bottom-4 -left-4 bg-forest-800 text-white rounded-2xl p-5 max-w-[220px] shadow-xl hidden sm:block">
            <p class="text-sm italic leading-relaxed text-forest-100">"<?php echo esc_html( sparsha_t( $founder_quote ) ); ?>"</p>
            <p class="text-amber-400 text-xs mt-2 font-medium">— <?php echo esc_html( $founder_name ); ?></p>
          </div>
        </div>
        <div class="reveal order-1 lg:order-2">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-4"><?php echo esc_html( sparsha_t( get_field( 'founder_label' ) ?: 'Meet the Founder' ) ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 leading-tight mb-6"><?php echo esc_html( $founder_name ); ?></h2>
          <?php if ( $founder_content ) : ?>
          <div class="text-forest-600 leading-relaxed space-y-5 mb-8"><?php echo wp_kses_post( $founder_content ); ?></div>
          <?php endif; ?>
          <?php if ( $founder_tags ) : ?>
          <div class="flex flex-wrap gap-3 mb-8">
            <?php foreach ( $founder_tags as $tag ) : ?>
            <span class="bg-forest-100 text-forest-700 text-sm font-medium px-4 py-2 rounded-full"><?php echo esc_html( sparsha_t( $tag['text'] ) ); ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <a href="<?php echo esc_url( function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? home_url( '/ro/contactati-ne/' ) : home_url( '/contact/' ) ); ?>" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-amber-100 font-semibold px-7 py-3.5 rounded-full transition-colors"><?php echo esc_html( sparsha_t( 'Book a Consultation' ) ); ?></a>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php
    $values_enabled = get_field( 'values_enable' ) !== false ? get_field( 'values_enable' ) : true;
    $values_items   = get_field( 'values_items' );
    $value_icons    = array(
      '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
      '<path d="M4.318 6.318a4.5 4.5 0 0 0 0 6.364L12 20.364l7.682-7.682a4.5 4.5 0 0 0-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 0 0-6.364 0z"/>',
      '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
      '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
    );
    if ( $values_enabled && $values_items ) :
    ?>
    <!-- Values -->
    <section class="py-20 px-6 bg-forest-50">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( sparsha_t( get_field( 'values_label' ) ?: 'What We Stand For' ) ); ?></p>
          <h2 class="font-serif text-4xl font-bold text-forest-900"><?php echo esc_html( sparsha_t( get_field( 'values_heading' ) ?: 'Our Core Values' ) ); ?></h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <?php foreach ( $values_items as $i => $value ) : ?>
          <div class="reveal bg-white rounded-2xl p-7 shadow-sm border border-forest-100 text-center">
            <div class="w-14 h-14 rounded-full bg-forest-100 flex items-center justify-center mx-auto mb-5"><svg class="w-7 h-7 text-forest-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><?php echo isset( $value_icons[ $i ] ) ? $value_icons[ $i ] : $value_icons[0]; ?></svg></div>
            <h3 class="font-serif text-lg font-semibold text-forest-900 mb-2"><?php echo esc_html( $value['title'] ); ?></h3>
            <p class="text-forest-600 text-sm leading-relaxed"><?php echo esc_html( $value['description'] ); ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php
    // ── Team ──
    $team_enabled = get_field( 'team_enable' ) !== false ? get_field( 'team_enable' ) : true;
    $team_members = get_field( 'team_members' );
    if ( $team_enabled && $team_members ) : ?>
    <section class="py-20 px-6 bg-cream">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14 reveal">
          <?php $t_label = get_field( 'team_label' ); if ( $t_label ) : ?>
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( $t_label ); ?></p>
          <?php endif; ?>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900"><?php echo esc_html( get_field( 'team_heading' ) ?: 'The People Behind Sparsha' ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
          <?php $t_desc = get_field( 'team_description' ); if ( $t_desc ) : ?>
          <p class="text-forest-600 max-w-2xl mx-auto"><?php echo esc_html( $t_desc ); ?></p>
          <?php endif; ?>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <?php foreach ( $team_members as $m ) :
            $m_img = $m['image'] ?? null;
            $m_img_url = is_array( $m_img ) ? ( $m_img['sizes']['medium_large'] ?? $m_img['url'] ) : '';
          ?>
          <article class="reveal bg-white rounded-3xl overflow-hidden shadow-sm border border-forest-100 hover:shadow-md transition-shadow flex flex-col">
            <?php if ( $m_img_url ) : ?>
            <div class="aspect-square overflow-hidden bg-forest-50">
              <img src="<?php echo esc_url( $m_img_url ); ?>" alt="<?php echo esc_attr( $m['name'] ?? '' ); ?>" class="w-full h-full object-cover" loading="lazy">
            </div>
            <?php else : ?>
            <div class="aspect-square bg-forest-100 flex items-center justify-center">
              <i class="fa-solid fa-user text-6xl text-forest-300"></i>
            </div>
            <?php endif; ?>
            <div class="p-6 flex flex-col flex-grow">
              <h3 class="font-serif text-2xl font-bold text-forest-900 mb-1"><?php echo esc_html( $m['name'] ?? '' ); ?></h3>
              <?php if ( ! empty( $m['role'] ) ) : ?>
              <p class="text-amber-600 text-xs font-semibold tracking-[0.15em] uppercase mb-4"><?php echo esc_html( $m['role'] ); ?></p>
              <?php endif; ?>
              <?php if ( ! empty( $m['bio'] ) ) : ?>
              <div class="text-forest-600 text-sm leading-relaxed space-y-3 [&_p]:mb-3 [&_p:last-child]:mb-0"><?php echo wp_kses_post( $m['bio'] ); ?></div>
              <?php endif; ?>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

  </main>

<?php get_footer(); ?>
