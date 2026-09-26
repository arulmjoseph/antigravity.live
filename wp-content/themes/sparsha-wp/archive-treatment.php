<?php
get_header();
$uri        = get_template_directory_uri();
$arch_label = get_field( 'treatment_archive_label', 'option' ) ?: 'Our Treatments';
$arch_head  = get_field( 'treatment_archive_heading', 'option' ) ?: 'Ayurvedic Treatments';
$arch_sub   = get_field( 'treatment_archive_sub', 'option' );
$arch_bg    = get_field( 'treatment_archive_banner', 'option' );
$banner_url = is_array( $arch_bg ) ? $arch_bg['url'] : sparsha_page_banner_url();
?>

  <!-- Hero -->
  <?php
  if ( ! $banner_url ) {
    $banner_url = $uri . '/assets/images/imgi_46_2.jpg';
  }
  ?>
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $banner_url ); ?>" alt="<?php echo esc_attr( $arch_head ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( sparsha_t( $arch_head ) ); ?></h1>
    </div>
  </section>

  <main>
    <section id="treatments" class="relative py-24 px-6 overflow-hidden bg-cream">
      <div class="max-w-7xl mx-auto">

        <div class="text-center mb-16 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( sparsha_t( $arch_label ) ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 mb-2"><?php echo esc_html( sparsha_t( $arch_head ) ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
          <?php if ( $arch_sub ) : ?>
          <p class="text-forest-600 max-w-2xl mx-auto"><?php echo esc_html( $arch_sub ); ?></p>
          <?php endif; ?>
        </div>

        <?php
        $parents = new WP_Query( array(
          'post_type'      => 'treatment',
          'post_status'    => 'publish',
          'post_parent'    => 0,
          'posts_per_page' => -1,
          'orderby'        => 'menu_order',
          'order'          => 'ASC',
        ) );
        if ( $parents->have_posts() ) :
        ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <?php $fi = 0; while ( $parents->have_posts() ) : $parents->the_post();
            get_template_part( 'template-parts/treatment-card', null, array( 'index' => $fi ) );
            $fi++;
          endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>

      </div>
    </section>

  </main>

<?php get_footer(); ?>
