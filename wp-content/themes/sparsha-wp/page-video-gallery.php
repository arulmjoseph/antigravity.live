<?php
/*
 * Template Name: Video Gallery
 * Template Post Type: page
 */
get_header();
$uri          = get_template_directory_uri();
$hero_image   = get_field( 'hero_image' );
$hero_img_url = $hero_image ? $hero_image['url'] : $uri . '/assets/images/imgi_46_2.jpg';
$hero_img_alt = $hero_image ? $hero_image['alt'] : 'Video Gallery';
$videos       = get_field( 'videos' );
$channel      = get_field( 'youtube_channel_url' ) ?: get_theme_mod( 'sparsha_youtube', '' );
?>

  <!-- Hero -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $hero_img_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( get_field( 'gallery_heading' ) ?: 'Video Gallery' ); ?></h1>
    </div>
  </section>

  <main>
    <section class="py-24 px-6 bg-cream">
      <div class="max-w-7xl mx-auto">

        <div class="text-center mb-16 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( sparsha_t( get_field( 'gallery_label' ) ?: 'Watch & Learn' ) ); ?></p>
          <h2 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900 mb-2"><?php echo esc_html( sparsha_t( get_field( 'gallery_subheading' ) ?: 'Guest Stories & Ayurvedic Wisdom' ) ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
        </div>

        <?php if ( $videos ) : ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
          <?php foreach ( $videos as $video ) :
            $raw_vid = trim( (string) ( $video['youtube_id'] ?? $video['video_id'] ?? '' ) );
            if ( ! $raw_vid ) continue;

            if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $raw_vid, $m ) ) {
              $vid = $m[1];
            } else {
              $vid = $raw_vid;
            }
          ?>
          <div class="reveal group flex flex-col">
            <div class="relative aspect-video w-full rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow bg-black ring-1 ring-forest-900/10">
              <iframe class="absolute inset-0 w-full h-full" src="https://www.youtube.com/embed/<?php echo esc_attr( $vid ); ?>?rel=0" title="<?php echo esc_attr( $video['title'] ?? 'Sparsha Ayurveda' ); ?>" loading="lazy" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
            <?php if ( ! empty( $video['title'] ) ) : ?>
            <h3 class="font-serif text-base font-semibold text-forest-900 mt-3 text-center"><?php echo esc_html( sparsha_t( $video['title'] ) ); ?></h3>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else : ?>
        <p class="text-center text-forest-500"><?php echo esc_html( sparsha_t( 'Videos coming soon. Add YouTube videos in the Video Gallery page editor.' ) ); ?></p>
        <?php endif; ?>

        <?php if ( $channel ) : ?>
        <div class="reveal mt-14 text-center">
          <a href="<?php echo esc_url( $channel ); ?>" target="_blank" rel="noopener" class="group inline-flex items-center gap-3 bg-red-600 hover:bg-red-500 text-white font-semibold px-7 py-3.5 rounded-full transition-all duration-300 shadow-lg shadow-red-900/30">
            <i class="fa-brands fa-youtube w-5 h-5"></i>
            <?php echo esc_html( sparsha_t( 'Visit Our YouTube Channel' ) ); ?>
          </a>
        </div>
        <?php endif; ?>

      </div>
    </section>
  </main>

<?php get_footer(); ?>
