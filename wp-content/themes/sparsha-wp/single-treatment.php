<?php
get_header();
$uri = get_template_directory_uri();
$L = array(
	'heading'   => sparsha_t( get_theme_mod( 'sparsha_tr_heading', 'Treatment Details' ) ),
	'duration'  => sparsha_t( get_theme_mod( 'sparsha_tr_duration_label', 'Duration' ) ),
	'price'     => sparsha_t( get_theme_mod( 'sparsha_tr_price_label', 'Price' ) ),
	'pricing'   => sparsha_t( get_theme_mod( 'sparsha_tr_pricing_label', 'Pricing Options' ) ),
	'book'      => sparsha_t( get_theme_mod( 'sparsha_tr_book_text', 'Book This Treatment' ) ),
	'call'      => sparsha_t( get_theme_mod( 'sparsha_tr_call_text', 'Call' ) ),
	'back'      => sparsha_t( get_theme_mod( 'sparsha_tr_back_text', 'All Treatments' ) ),
	'gallery'   => sparsha_t( get_theme_mod( 'sparsha_tr_gallery_label', 'Treatment Gallery' ) ),
);
?>

  <!-- Hero -->
  <?php
  $banner_url = sparsha_page_banner_url();
  if ( ! $banner_url && has_post_thumbnail() ) {
    $banner_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
  }
  if ( ! $banner_url ) {
    $banner_url = $uri . '/assets/images/imgi_47_3.jpg';
  }
  $booking   = get_field( 'treatment_booking_link' );
  $book_url  = $booking ? $booking['url'] : home_url( '/contact' );
  $book_text = $booking && ! empty( $booking['title'] ) ? $booking['title'] : $L['book'];
  $subtitle  = get_field( 'treatment_subtitle' );
  ?>
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $banner_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <?php if ( $subtitle ) : ?>
      <span class="inline-block bg-amber-500/20 text-amber-300 text-xs font-semibold px-3 py-1 rounded-full border border-amber-500/30 mb-3"><?php echo esc_html( $subtitle ); ?></span>
      <?php endif; ?>
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php the_title(); ?></h1>
    </div>
  </section>

  <main>
    <section class="py-20 px-6 bg-cream">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-3 gap-12">

        <!-- Main content -->
        <div class="lg:col-span-2">
          <?php if ( has_post_thumbnail() ) : ?>
          <figure class="reveal mb-8 rounded-2xl overflow-hidden shadow-sm">
            <?php the_post_thumbnail( 'large', array( 'class' => 'w-full h-auto object-cover', 'loading' => 'eager' ) ); ?>
          </figure>
          <?php endif; ?>
          <?php
          $therapies = get_field( 'treatment_therapies' );
          $content   = get_field( 'treatment_content' );
          if ( $content ) : ?>
          <article class="reveal sparsha-article mb-10">
            <?php echo wp_kses_post( $content ); ?>
          </article>
          <?php endif; ?>
          <?php if ( $therapies ) : ?>
          <div class="reveal space-y-12">
            <?php foreach ( $therapies as $i => $t ) :
              $img     = $t['image'] ?? null;
              $img_url = is_array( $img ) ? ( $img['sizes']['medium_large'] ?? $img['url'] ) : '';
              $img_alt = is_array( $img ) ? ( $img['alt'] ?? '' ) : '';
              $cta     = $t['cta'] ?? null;
              $reverse = ( $i % 2 === 1 );
            ?>
            <article class="grid md:grid-cols-2 gap-6 md:gap-8 items-center">
              <?php if ( $img_url ) : ?>
              <div class="<?php echo $reverse ? 'md:order-2' : ''; ?>">
                <div class="rounded-2xl overflow-hidden shadow-sm">
                  <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ?: $t['title'] ); ?>" class="w-full h-auto object-cover" loading="lazy">
                </div>
              </div>
              <?php endif; ?>
              <div class="<?php echo ( $img_url && $reverse ) ? 'md:order-1' : ''; ?>">
                <?php if ( ! empty( $t['title'] ) ) : ?>
                <h3 class="font-serif text-2xl md:text-3xl font-bold text-forest-900 mb-2"><?php echo esc_html( $t['title'] ); ?></h3>
                <?php endif; ?>
                <?php if ( ! empty( $t['description'] ) ) : ?>
                <p class="text-forest-600 leading-relaxed mb-3"><?php echo esc_html( $t['description'] ); ?></p>
                <?php endif; ?>
                <?php if ( ! empty( $t['meta'] ) ) : ?>
                <p class="text-amber-700 font-semibold text-sm"><?php echo esc_html( $t['meta'] ); ?></p>
                <?php endif; ?>
                <?php if ( $cta && ! empty( $cta['url'] ) ) : ?>
                <a href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo ( $cta['target'] ?? '' ) === '_blank' ? ' target="_blank" rel="noopener"' : ''; ?> class="mt-4 inline-flex items-center gap-2 bg-forest-800 hover:bg-forest-900 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-full transition-colors">
                  <?php echo esc_html( $cta['title'] ?: sparsha_t( 'Learn More' ) ); ?>
                  <i class="fa-solid fa-arrow-right"></i>
                </a>
                <?php endif; ?>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
          <?php elseif ( ! $content ) : ?>
          <article class="reveal sparsha-article">
            <?php the_content(); ?>
          </article>
          <?php endif; ?>

          <?php $note = get_field( 'treatment_note' ); if ( $note ) : ?>
          <div class="mt-6 p-4 bg-forest-50 rounded-xl border border-forest-100/50 text-sm text-forest-600 italic">
            <?php echo esc_html( $note ); ?>
          </div>
          <?php endif; ?>

          <?php $variants = get_field( 'treatment_price_variants' ); if ( $variants ) : ?>
          <div class="mt-8 p-5 bg-forest-50 rounded-xl border border-forest-100/50">
            <h4 class="font-serif font-semibold text-forest-900 mb-3"><?php echo esc_html( $L['pricing'] ); ?></h4>
            <div class="space-y-2 text-sm">
              <?php foreach ( $variants as $v ) : ?>
              <div class="flex items-center justify-between">
                <span class="text-forest-600"><?php echo esc_html( $v['duration'] ); ?><?php if ( ! empty( $v['note'] ) ) : ?> <span class="text-forest-400">— <?php echo esc_html( $v['note'] ); ?></span><?php endif; ?></span>
                <span class="font-bold text-forest-900"><?php echo esc_html( $v['price'] ); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
          <div class="reveal bg-forest-900 rounded-2xl p-6 text-white sticky top-24">
            <h4 class="font-serif text-lg font-semibold text-amber-300 mb-5"><?php echo esc_html( $L['heading'] ); ?></h4>
            <div class="space-y-4 text-sm">
              <?php $duration = get_field( 'treatment_duration' ); if ( $duration ) : ?>
              <div class="flex items-start gap-3 pb-4 border-b border-forest-800">
                <i class="fa-regular fa-clock w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5"></i>
                <div><p class="text-forest-400 text-xs mb-0.5"><?php echo esc_html( $L['duration'] ); ?></p><p class="font-medium"><?php echo esc_html( $duration ); ?></p></div>
              </div>
              <?php endif; ?>
              <?php $price = get_field( 'treatment_price' ); if ( $price ) : ?>
              <div class="flex items-start gap-3">
                <i class="fa-solid fa-tag w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5"></i>
                <div><p class="text-forest-400 text-xs mb-0.5"><?php echo esc_html( $L['price'] ); ?></p><p class="font-medium"><?php echo esc_html( $price ); ?></p></div>
              </div>
              <?php endif; ?>
            </div>
            <a href="<?php echo esc_url( $book_url ); ?>" class="mt-6 w-full block text-center bg-amber-500 hover:bg-amber-400 text-bark font-semibold py-3.5 rounded-xl transition-colors"><?php echo esc_html( $book_text ); ?></a>
            <a href="tel:<?php echo esc_attr( get_theme_mod( 'sparsha_phone_href', '+36705625113' ) ); ?>" class="mt-3 w-full block text-center border border-amber-500/60 hover:border-amber-500 bg-forest-800 hover:bg-forest-700 text-white text-sm font-medium py-3 rounded-xl transition-colors">
              <?php echo esc_html( $L['call'] ); ?> <?php echo esc_html( get_theme_mod( 'sparsha_phone', '+36 70 562 5113' ) ); ?>
            </a>
          </div>

          <!-- Back to all treatments -->
          <div class="reveal bg-white rounded-2xl p-6 border border-forest-100 shadow-sm">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'treatment' ) ); ?>" class="flex items-center gap-2 text-sm text-forest-600 hover:text-amber-600 transition-colors font-medium">
              <i class="fa-solid fa-arrow-left w-4 h-4"></i>
              <?php echo esc_html( $L['back'] ); ?>
            </a>
          </div>
        </aside>

      </div>
    </section>

    <?php
    // ── Child treatments — alternating image/content rows ──
    $children = get_posts( array(
      'post_type'      => 'treatment',
      'post_status'    => 'publish',
      'post_parent'    => get_the_ID(),
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
      'posts_per_page' => -1,
    ) );
    if ( $children ) : ?>
    <section class="py-16 px-6 bg-white">
      <div class="max-w-6xl mx-auto space-y-14">
        <?php foreach ( $children as $i => $c ) :
          $ci      = $i + 1;
          $c_id    = $c->ID;
          $c_thumb = get_the_post_thumbnail_url( $c_id, 'large' );
          $c_short = get_field( 'treatment_short_description', $c_id ) ?: get_the_excerpt( $c_id );
          $c_dur   = get_field( 'treatment_duration', $c_id );
          $c_price = get_field( 'treatment_price', $c_id );
          $reverse = ( $i % 2 === 1 );
        ?>
        <article class="reveal grid md:grid-cols-2 gap-8 md:gap-12 items-center">
          <div class="<?php echo $reverse ? 'md:order-2' : ''; ?>">
            <?php if ( $c_thumb ) : ?>
            <a href="<?php echo esc_url( get_permalink( $c_id ) ); ?>" class="block rounded-2xl overflow-hidden shadow-sm">
              <img src="<?php echo esc_url( $c_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $c_id ) ); ?>" class="w-full h-auto object-cover" loading="lazy">
            </a>
            <?php endif; ?>
          </div>
          <div class="<?php echo $reverse ? 'md:order-1' : ''; ?>">
            <h3 class="font-serif text-2xl md:text-3xl font-bold text-forest-900 mb-3">
              <a href="<?php echo esc_url( get_permalink( $c_id ) ); ?>" class="hover:text-amber-600 transition-colors"><?php echo esc_html( get_the_title( $c_id ) ); ?></a>
            </h3>
            <?php if ( $c_dur || $c_price ) : ?>
            <div class="flex flex-wrap items-center gap-4 text-sm text-forest-500 mb-4">
              <?php if ( $c_dur ) : ?><span><i class="fa-regular fa-clock text-amber-500 mr-1"></i><?php echo esc_html( $c_dur ); ?></span><?php endif; ?>
              <?php if ( $c_price ) : ?><span class="font-semibold text-forest-900"><?php echo esc_html( $c_price ); ?></span><?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if ( $c_short ) : ?>
            <p class="text-forest-600 leading-relaxed mb-5"><?php echo esc_html( $c_short ); ?></p>
            <?php endif; ?>
            <a href="<?php echo esc_url( get_permalink( $c_id ) ); ?>" class="inline-flex items-center gap-2 bg-forest-800 hover:bg-forest-900 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-full transition-colors">
              <?php echo esc_html( sparsha_t( 'Know More' ) ); ?>
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php
    $gallery = get_field( 'treatment_gallery' );
    // Only show the gallery if there is more than one image (since the featured image is already shown in the hero)
    if ( $gallery && count( $gallery ) > 1 ) :
    ?>
    <!-- Gallery with Lightbox -->
    <section class="py-16 px-6 bg-forest-50">
      <div class="max-w-7xl mx-auto">
        <h3 class="font-serif text-3xl font-bold text-forest-900 mb-8 reveal"><?php echo esc_html( $L['gallery'] ); ?></h3>
        <div class="sparsha-lightbox-gallery grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <?php foreach ( $gallery as $i => $img ) :
            $thumb = isset( $img['sizes']['medium_large'] ) ? $img['sizes']['medium_large'] : ( isset( $img['sizes']['medium'] ) ? $img['sizes']['medium'] : $img['url'] );
            $full  = isset( $img['sizes']['large'] ) ? $img['sizes']['large'] : $img['url'];
          ?>
          <a href="<?php echo esc_url( $full ); ?>" class="sparsha-lightbox-item reveal block rounded-2xl overflow-hidden aspect-square relative group" data-index="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( $img['alt'] ?: 'Treatment photo ' . ( $i + 1 ) ); ?>">
            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
            <div class="absolute inset-0 bg-forest-900/0 group-hover:bg-forest-900/40 transition-colors flex items-center justify-center">
              <i class="fa-solid fa-magnifying-glass-plus w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

  </main>

<?php get_footer(); ?>
