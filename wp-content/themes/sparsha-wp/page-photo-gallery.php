<?php
/*
 * Template Name: Photo Gallery
 * Template Post Type: page
 */
get_header();
$uri          = get_template_directory_uri();
$hero_image   = get_field( 'hero_image' );
$hero_img_url = $hero_image ? $hero_image['url'] : $uri . '/assets/images/imgi_47_3.jpg';
$hero_img_alt = $hero_image ? $hero_image['alt'] : 'Photo Gallery';
$gallery      = get_field( 'gallery_images' );
?>

  <!-- Hero -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $hero_img_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( get_field( 'gallery_heading' ) ?: 'Photo Gallery' ); ?></h1>
    </div>
  </section>

  <main>
    <section class="py-24 px-6 bg-cream">
      <div class="max-w-7xl mx-auto">

        <div class="text-center mb-16 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( get_field( 'gallery_label' ) ?: 'Our Centre & Treatments' ); ?></p>
          <h2 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900 mb-2"><?php echo esc_html( get_field( 'gallery_subheading' ) ?: 'Moments of Healing' ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
        </div>

        <?php if ( $gallery ) : ?>
        <div class="sparsha-lightbox-gallery columns-1 sm:columns-2 lg:columns-3 gap-4 space-y-4">
          <?php foreach ( $gallery as $i => $img ) :
            $thumb = $img['sizes']['medium_large'] ?? ( $img['sizes']['large'] ?? $img['url'] );
            $full  = $img['sizes']['large'] ?? $img['url'];
          ?>
          <a href="<?php echo esc_url( $full ); ?>" class="sparsha-lightbox-item block group relative overflow-hidden rounded-2xl break-inside-avoid" data-index="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( $img['alt'] ?: 'Photo ' . ( $i + 1 ) ); ?>">
            <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
            <div class="absolute inset-0 bg-forest-900/0 group-hover:bg-forest-900/30 transition-colors flex items-center justify-center">
              <i class="fa-solid fa-magnifying-glass-plus w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php else : ?>
        <p class="text-center text-forest-500"><?php esc_html_e( 'Photos coming soon. Add images in the Photo Gallery page editor.', 'sparsha-wp' ); ?></p>
        <?php endif; ?>

      </div>
    </section>
  </main>

<?php get_footer(); ?>
