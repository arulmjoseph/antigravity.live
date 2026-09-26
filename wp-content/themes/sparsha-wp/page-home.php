<?php
/*
 * Template Name: Home
 * Template Post Type: page
 */
get_header();
$uri = get_template_directory_uri();

// ACF image helper
function sparsha_acf_img( $field_name, $fallback_file = '' ) {
	$img = get_field( $field_name );
	if ( $img && is_array( $img ) && ! empty( $img['url'] ) ) {
		return $img;
	}
	$uri = get_template_directory_uri();
	return array(
		'url' => $fallback_file ? $uri . '/assets/images/' . $fallback_file : '',
		'alt' => '',
	);
}

// Read ACF data for about section image
$about_image = get_field( 'about_image' );
$about_img_url = ( $about_image && ! empty( $about_image['url'] ) ) ? $about_image['url'] : $uri . '/assets/images/imgi_47_3.jpg';
$about_img_alt = ( $about_image && ! empty( $about_image['alt'] ) ) ? $about_image['alt'] : 'Shirodhara Ayurvedic treatment';

// Read ACF data for healthy section image
$healthy_image = get_field( 'healthy_image' );
$healthy_img_url = ( $healthy_image && ! empty( $healthy_image['url'] ) ) ? $healthy_image['url'] : $uri . '/assets/images/imgi_48_4.jpg';
$healthy_img_alt = ( $healthy_image && ! empty( $healthy_image['alt'] ) ) ? $healthy_image['alt'] : 'Ayurvedic treatment';

// Read ACF data for founder image
$founder_image = get_field( 'founder_image' );
$founder_img_url = ( $founder_image && ! empty( $founder_image['url'] ) ) ? $founder_image['url'] : $uri . '/assets/images/imgi_47_3.jpg';
$founder_img_alt = ( $founder_image && ! empty( $founder_image['alt'] ) ) ? $founder_image['alt'] : 'Dr. Girish Mokeri';
?>


  <main>
    <!-- ===== Home: Hero Section ===== -->
    <?php
    $hero_slides = get_field( 'hero_slides' );
    $hero_fallback_imgs = array(
      'imgi_49_Detox-Your-Body-With-Ayurveda.jpg',
      'imgi_46_2.jpg',
      'imgi_47_3.jpg',
      'imgi_48_4.jpg',
    );
    ?>
    <div class="swiper hero-swiper">
      <div class="swiper-wrapper">

        <?php if ( $hero_slides ) : foreach ( $hero_slides as $si => $slide ) :
          $slide_img = ( ! empty( $slide['image']['url'] ) ) ? $slide['image']['url'] : $uri . '/assets/images/' . $hero_fallback_imgs[ $si % count( $hero_fallback_imgs ) ];
          $slide_alt = ( ! empty( $slide['image']['alt'] ) ) ? $slide['image']['alt'] : $slide['heading'];
          $cta1       = $slide['cta_primary'] ?? array();
          $cta2       = $slide['cta_secondary'] ?? array();
          $is_ro_page = ( function_exists( 'pll_current_language' ) && pll_current_language( 'slug' ) === 'ro' ) || ( isset( $_SERVER['REQUEST_URI'] ) && preg_match( '#/ro(/|$)#i', $_SERVER['REQUEST_URI'] ) );

          $cta1_url   = ! empty( $cta1['url'] ) ? $cta1['url'] : ( $is_ro_page ? home_url( '/ro/contactati-ne/' ) : home_url( '/contact/' ) );
          $cta1_title = ! empty( $cta1['title'] ) ? $cta1['title'] : 'Book Appointment';

          $cta2_url   = ! empty( $cta2['url'] ) ? $cta2['url'] : ( $is_ro_page ? home_url( '/ro/treatments/' ) : home_url( '/treatments/' ) );
          $cta2_title = ! empty( $cta2['title'] ) ? $cta2['title'] : 'Explore Services';

          $hl_color = ! empty( $slide['highlight_color'] ) ? $slide['highlight_color'] : '#fcd34d';
        ?>
        <div class="swiper-slide relative">
          <img src="<?php echo esc_url( $slide_img ); ?>" alt="<?php echo esc_attr( $slide_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center">
          <div class="absolute inset-0 bg-gradient-to-t from-forest-900/90 via-forest-900/45 to-forest-900/15 md:bg-gradient-to-b md:from-forest-900/55 md:via-forest-900/45 md:to-forest-900/70"></div>
          <div class="relative h-full flex items-end md:items-center justify-center text-center px-4 sm:px-6 pb-12 sm:pb-16 md:pb-0">
            <div class="max-w-3xl mx-auto w-full pt-16 md:pt-20">
              <?php if ( ! empty( $slide['label'] ) ) : ?>
              <p class="slide-label text-amber-400 text-xs sm:text-sm font-semibold tracking-[0.2em] uppercase mb-2.5 sm:mb-4">
                <?php echo esc_html( sparsha_t( $slide['label'] ) ); ?>
              </p>
              <?php endif; ?>
              <h1 class="slide-heading font-serif text-lg sm:text-2xl md:text-[2rem] font-bold text-white leading-tight sm:leading-snug mb-2.5 sm:mb-4 max-w-2xl mx-auto">
                <?php echo esc_html( sparsha_t( $slide['heading'] ) ); ?><?php if ( ! empty( $slide['highlight'] ) ) : ?><br><em class="not-italic" style="color: <?php echo esc_attr( $hl_color ); ?>;"><?php echo esc_html( sparsha_t( $slide['highlight'] ) ); ?></em><?php endif; ?>
              </h1>
              <?php if ( ! empty( $slide['description'] ) ) : ?>
              <p class="slide-sub text-white/90 text-xs sm:text-base md:text-lg max-w-xl mx-auto mb-4 sm:mb-6 md:mb-10 line-clamp-2 sm:line-clamp-none"><?php echo esc_html( sparsha_t( $slide['description'] ) ); ?></p>
              <?php endif; ?>
              <div class="slide-cta flex flex-wrap gap-2.5 sm:gap-4 justify-center mb-2 md:mb-0">
                <a href="<?php echo esc_url( $cta1_url ); ?>" class="bg-amber-500 hover:bg-amber-400 text-bark text-xs sm:text-sm font-semibold px-5 sm:px-8 py-2 sm:py-3.5 rounded-full transition-colors shadow-lg"><?php echo esc_html( sparsha_t( $cta1_title ) ); ?></a>
                <a href="<?php echo esc_url( $cta2_url ); ?>" class="border border-white/40 hover:border-amber-400 hover:text-amber-300 text-white text-xs sm:text-sm font-medium px-5 sm:px-8 py-2 sm:py-3.5 rounded-full transition-colors backdrop-blur-sm"><?php echo esc_html( sparsha_t( $cta2_title ) ); ?></a>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

      </div><!-- /swiper-wrapper -->

      <!-- Pagination dots -->
      <div class="swiper-pagination"></div>

      <!-- Nav arrows -->
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>

    <!-- ═══════════════════════════════════════════ ABOUT ══════════════════════════════════════════ -->
    <section id="about" class="pt-16 pb-8 md:pt-20 md:pb-10 lg:pt-20 lg:pb-10 px-6 bg-cream">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">

        <!-- Visual block -->
        <div class="reveal relative">
          <div class="rounded-3xl overflow-hidden bg-forest-100 aspect-[4/3]">
            <img src="<?php echo esc_url( $about_img_url ); ?>" alt="<?php echo esc_attr( $about_img_alt ); ?>" class="w-full h-full object-cover">
          </div>
          <!-- accent badge -->
          <div class="absolute -bottom-6 -right-4 bg-amber-500 text-bark rounded-2xl px-6 py-4 shadow-xl shadow-amber-900/20">
            <p class="font-serif font-bold text-2xl"><?php echo esc_html( get_field( 'about_badge_number' ) ?: '12+' ); ?></p>
            <p class="text-xs font-semibold"><?php echo esc_html( sparsha_t( get_field( 'about_badge_text' ) ?: 'Years in Europe' ) ); ?></p>
          </div>
          <!-- Kerala origin tag -->
          <div class="absolute top-6 -left-4 bg-forest-800 text-amber-200 rounded-xl px-4 py-3 shadow-lg text-xs font-medium">
            🌿 <?php echo esc_html( sparsha_t( get_field( 'about_origin_tag' ) ?: 'Rooted in Kerala, India' ) ); ?>
          </div>
        </div>

        <!-- Text -->
        <div class="reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( get_field( 'about_label' ) ?: 'About Sparsha' ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 leading-tight mb-2">
            <?php echo wp_kses_post( get_field( 'about_heading' ) ?: 'Why You Choose <em>Sparsha Ayurveda!</em>' ); ?>
          </h2>
          <div class="flex items-center gap-4 mt-3 mb-7">
            <div class="h-[1px] bg-forest-900/20 w-20"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            <div class="h-[1px] bg-forest-900/20 w-20"></div>
          </div>
          <div class="text-forest-600 leading-relaxed space-y-5 mb-8 [&_strong]:text-forest-800">
            <?php echo wp_kses_post( get_field( 'about_content' ) ); ?>
          </div>

          <?php $about_cta = get_field( 'about_cta' ); ?>
          <a href="<?php echo esc_url( $about_cta['url'] ?? home_url( '/contact' ) ); ?>" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-amber-100 font-semibold px-7 py-3.5 rounded-full transition-colors shadow-sm mt-4">
            <?php echo esc_html( $about_cta['title'] ?? 'Book an Appointment' ); ?>
          </a>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════ SERVICES ══════════════════════════════════════════ -->
    <section id="services" class="relative pt-8 pb-16 md:pt-10 md:pb-20 lg:pt-10 lg:pb-20 px-6 overflow-hidden">
      <!-- Watercolor leaf background -->
      <div class="absolute inset-0 -z-10">
        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/13723074_5332433.jpg" alt="" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-cream/75"></div>
      </div>
      <div class="max-w-7xl mx-auto">

        <div class="text-center mb-16 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( get_field( 'services_label' ) ?: 'Our Treatments' ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 mb-2"><?php echo esc_html( get_field( 'services_heading' ) ?: 'Experience the Bliss of Ayurveda With Us!' ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <div class="flex items-center justify-center text-forest-700 shrink-0">
              <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            </div>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
          <?php $services_desc = get_field( 'services_description' ); if ( $services_desc ) : ?>
          <p class="text-forest-600 max-w-2xl mx-auto"><?php echo esc_html( $services_desc ); ?></p>
          <?php endif; ?>
        </div>

        <?php
        $services_count = absint( get_field( 'services_count' ) );
        $home_treatments = new WP_Query( array(
          'post_type'      => 'treatment',
          'post_status'    => 'publish',
          'post_parent'    => 0,
          'posts_per_page' => $services_count ?: -1,
          'orderby'        => 'menu_order',
          'order'          => 'ASC',
        ) );
        if ( $home_treatments->have_posts() ) :
        ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <?php $hi = 0; while ( $home_treatments->have_posts() ) : $home_treatments->the_post();
            get_template_part( 'template-parts/treatment-card', null, array( 'index' => $hi ) );
            $hi++;
          endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>

        <?php
        $services_cta = get_field( 'services_cta' );
        $sc_url  = $services_cta['url'] ?? get_post_type_archive_link( 'treatment' );
        $sc_text = $services_cta['title'] ?? sparsha_label( 'label_view_all', 'View All Treatments' );
        ?>
        <div class="text-center mt-12 reveal">
          <a href="<?php echo esc_url( $sc_url ); ?>" class="inline-flex items-center gap-2 border border-forest-300 hover:bg-forest-700 hover:text-white hover:border-forest-700 text-forest-700 font-medium px-8 py-3.5 rounded-full transition-all duration-300">
            <?php echo esc_html( $sc_text ); ?>
          </a>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════ DELIGHTED GUESTS (YOUTUBE) ══════════════════════════════════════════ -->
    <?php
    $yt_enable = get_field( 'youtube_enable' );
    if ( $yt_enable !== false ) :
      $yt_videos  = get_field( 'youtube_videos' );
      $yt_channel = get_field( 'youtube_channel_url' ) ?: get_theme_mod( 'sparsha_youtube', 'https://www.youtube.com/channel/UC_hTPa9a8_gnHSOKD1JMh5g' );

      // Default fallback 6 videos if repeater is empty
      if ( empty( $yt_videos ) ) {
        $single_id = get_field( 'youtube_video_id' ) ?: 'MpjKyJEzNUQ';
        $yt_videos = array(
          array( 'video_id' => $single_id,    'title' => 'Natural Fertility with Ayurveda' ),
          array( 'video_id' => '4ix14Q4auNg', 'title' => 'Ayurvedic Healing & Panchakarma' ),
          array( 'video_id' => '7b3XoZh4cd0', 'title' => 'Guest Experience & Recovery' ),
          array( 'video_id' => 'D6QDDRhYgFQ', 'title' => 'Traditional Kerala Therapies' ),
          array( 'video_id' => 'EqdFfumyZ10', 'title' => 'Holistic Detox & Rejuvenation' ),
          array( 'video_id' => 'imfEHCIpWUo', 'title' => 'Ancient Wisdom for Modern Health' ),
        );
      }
    ?>
    <section class="relative py-20 px-6 overflow-hidden">
      <!-- Watercolor botanical background, very light -->
      <div class="absolute inset-0">
        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/bg2.jpg" alt="" class="w-full h-full object-cover object-center" style="background-repeat: no-repeat;">
        <div class="absolute inset-0 bg-cream/75"></div>
      </div>

      <div class="relative max-w-7xl mx-auto text-center">
        <div class="reveal mb-12">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( sparsha_t( get_field( 'youtube_label' ) ?: 'Real Experiences' ) ); ?></p>
          <h2 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900 mb-2"><?php echo esc_html( sparsha_t( get_field( 'youtube_heading' ) ?: 'Our Delighted Guests Says' ) ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <div class="flex items-center justify-center shrink-0">
              <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            </div>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
          <p class="text-forest-600 text-base sm:text-lg max-w-2xl mx-auto"><?php echo esc_html( sparsha_t( get_field( 'youtube_description' ) ?: 'Hear from our guests about their transformative Ayurvedic journeys at Sparsha — straight from their hearts.' ) ); ?></p>
        </div>

        <!-- 3x2 Grid (6 Videos) -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
          <?php foreach ( $yt_videos as $vi => $vitem ) :
            $raw_vid = trim( (string) ( $vitem['video_id'] ?? $vitem['youtube_id'] ?? '' ) );
            if ( ! $raw_vid ) continue;

            // Extract 11-char YouTube ID if full URL was pasted
            if ( preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $raw_vid, $m ) ) {
              $clean_id = $m[1];
            } else {
              $clean_id = $raw_vid;
            }
            $vtitle = ! empty( $vitem['title'] ) ? $vitem['title'] : 'Guest Experience Video ' . ( $vi + 1 );
          ?>
          <div class="reveal group flex flex-col">
            <div class="relative aspect-video w-full rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow bg-black ring-1 ring-forest-900/10">
              <iframe
                class="absolute inset-0 w-full h-full"
                src="https://www.youtube.com/embed/<?php echo esc_attr( $clean_id ); ?>?rel=0"
                title="<?php echo esc_attr( $vtitle ); ?>"
                loading="lazy"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen></iframe>
            </div>
            <?php if ( ! empty( $vitem['title'] ) ) : ?>
            <h3 class="font-serif text-sm sm:text-base font-semibold text-forest-900 mt-3 text-center"><?php echo esc_html( sparsha_t( $vitem['title'] ) ); ?></h3>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Channel link -->
        <?php if ( $yt_channel ) : ?>
        <div class="reveal mt-12 text-center">
          <p class="text-forest-600 text-sm mb-5"><?php echo esc_html( sparsha_t( 'Watch more guest stories & Ayurvedic wisdom on our YouTube channel' ) ); ?></p>
          <a href="<?php echo esc_url( $yt_channel ); ?>" target="_blank" rel="noopener noreferrer"
             class="group inline-flex items-center gap-3 bg-red-600 hover:bg-red-500 text-white font-semibold px-7 py-3.5 rounded-full transition-all duration-300 shadow-lg shadow-red-900/30">
            <i class="fa-brands fa-youtube w-5 h-5"></i>
            <?php echo esc_html( sparsha_t( 'Visit Our YouTube Channel' ) ); ?>
            <i class="fa-solid fa-arrow-right w-4 h-4 transition-transform group-hover:translate-x-0.5"></i>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════ STAY HEALTHY ══════════════════════════════════════════ -->
    <section id="panchakarma" class="py-24 px-6 bg-white">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">

        <!-- Image -->
        <div class="reveal relative">
          <div class="rounded-3xl overflow-hidden aspect-[4/5] lg:aspect-[3/4] shadow-2xl shadow-forest-900/20">
            <img src="<?php echo esc_url( $healthy_img_url ); ?>" alt="<?php echo esc_attr( $healthy_img_alt ); ?>" class="w-full h-full object-cover">
          </div>
          <!-- floating accent -->
          <div class="absolute -bottom-5 -right-5 bg-amber-500 text-bark rounded-2xl px-6 py-4 shadow-xl shadow-amber-900/20 hidden sm:block">
            <p class="font-serif font-bold text-2xl leading-none">12+</p>
            <p class="text-xs font-semibold mt-0.5"><?php echo esc_html( sparsha_t( 'Years of Healing' ) ); ?></p>
          </div>
        </div>

        <!-- Text -->
        <div class="reveal">
          <p class="text-amber-600 text-xs font-bold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( get_field( 'healthy_label' ) ?: 'Stay Healthy' ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 leading-tight mb-2">
            <?php echo wp_kses_post( get_field( 'healthy_heading' ) ?: 'With Ayurveda Treatment At Sparsha Ayurveda' ); ?>
          </h2>
          <div class="flex items-center gap-4 mt-3 mb-8">
            <div class="h-[1px] bg-forest-900/20 w-20"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            <div class="h-[1px] bg-forest-900/20 w-20"></div>
          </div>

          <div class="space-y-5 text-forest-600 leading-relaxed [&_em]:italic">
            <?php echo wp_kses_post( get_field( 'healthy_content' ) ); ?>
          </div>

          <?php $h_cta1 = get_field( 'healthy_cta_primary' ); $h_cta2 = get_field( 'healthy_cta_secondary' ); ?>
          <div class="flex flex-wrap gap-4 mt-10">
            <a href="<?php echo esc_url( $h_cta1['url'] ?? get_post_type_archive_link( 'treatment' ) ); ?>" class="bg-forest-700 hover:bg-forest-800 text-amber-100 font-semibold px-7 py-3.5 rounded-full transition-colors shadow-sm">
              <?php echo esc_html( $h_cta1['title'] ?? 'Our Services' ); ?>
            </a>
            <a href="<?php echo esc_url( $h_cta2['url'] ?? home_url( '/contact' ) ); ?>" class="border border-forest-300 hover:bg-forest-700 hover:text-white hover:border-forest-700 text-forest-700 font-medium px-7 py-3.5 rounded-full transition-all duration-300">
              <?php echo esc_html( $h_cta2['title'] ?? 'Book Appointment' ); ?>
            </a>
          </div>
        </div>

      </div>
    </section>

    <!-- ═══════════════════════════════════════════ WELCOME CTA STRIP ══════════════════════════════════════════ -->
    <section class="relative py-20 px-6 overflow-hidden" style="background: linear-gradient(135deg, #f5ebd9 0%, #ead9bc 50%, #d9c89f 100%);">

      <!-- soft watercolor blobs -->
      <div class="absolute -top-10 -left-16 w-72 h-72 rounded-full opacity-30 blur-3xl" style="background: radial-gradient(circle, #8cc872 0%, transparent 70%);"></div>
      <div class="absolute -bottom-12 -right-20 w-80 h-80 rounded-full opacity-30 blur-3xl" style="background: radial-gradient(circle, #d97706 0%, transparent 70%);"></div>

      <!-- left botanical accent -->
      <svg class="hidden md:block absolute left-4 lg:left-12 top-1/2 -translate-y-1/2 w-32 lg:w-44 opacity-[0.35] pointer-events-none" viewBox="0 0 200 320" fill="none" stroke="#285a14" stroke-width="1.4" stroke-linecap="round">
        <!-- stem -->
        <path d="M100 320 C 110 240 90 160 100 80 C 105 50 100 30 100 10"/>
        <!-- left leaves -->
        <path d="M100 280 C 60 270 30 245 22 210 C 55 220 85 245 100 280Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 220 C 65 215 40 195 35 165 C 65 175 92 195 100 220Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 160 C 72 158 52 140 48 115 C 72 124 95 142 100 160Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 105 C 80 105 65 90 62 70 C 80 76 96 92 100 105Z" fill="#479228" fill-opacity="0.35"/>
        <!-- right leaves -->
        <path d="M100 250 C 140 245 170 222 180 188 C 145 196 115 220 100 250Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 190 C 135 188 160 168 168 140 C 140 148 110 168 100 190Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 130 C 128 128 148 112 154 90 C 130 96 108 112 100 130Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 75 C 120 73 134 60 138 42 C 120 48 104 60 100 75Z" fill="#479228" fill-opacity="0.35"/>
      </svg>

      <!-- right botanical accent (mirrored, smaller) -->
      <svg class="hidden md:block absolute right-4 lg:right-12 top-1/2 -translate-y-1/2 w-28 lg:w-36 opacity-[0.3] pointer-events-none scale-x-[-1]" viewBox="0 0 200 320" fill="none" stroke="#285a14" stroke-width="1.4" stroke-linecap="round">
        <path d="M100 320 C 110 240 90 160 100 80 C 105 50 100 30 100 10"/>
        <path d="M100 280 C 60 270 30 245 22 210 C 55 220 85 245 100 280Z" fill="#347319" fill-opacity="0.3"/>
        <path d="M100 220 C 65 215 40 195 35 165 C 65 175 92 195 100 220Z" fill="#347319" fill-opacity="0.3"/>
        <path d="M100 160 C 72 158 52 140 48 115 C 72 124 95 142 100 160Z" fill="#347319" fill-opacity="0.3"/>
        <path d="M100 250 C 140 245 170 222 180 188 C 145 196 115 220 100 250Z" fill="#347319" fill-opacity="0.3"/>
        <path d="M100 190 C 135 188 160 168 168 140 C 140 148 110 168 100 190Z" fill="#347319" fill-opacity="0.3"/>
        <path d="M100 130 C 128 128 148 112 154 90 C 130 96 108 112 100 130Z" fill="#347319" fill-opacity="0.3"/>
      </svg>

      <!-- gold splash dots scattered -->
      <svg class="absolute inset-0 w-full h-full opacity-50 pointer-events-none" preserveAspectRatio="none" viewBox="0 0 1400 300">
        <circle cx="180" cy="60" r="3" fill="#d97706" opacity="0.4"/>
        <circle cx="200" cy="80" r="1.5" fill="#d97706" opacity="0.5"/>
        <circle cx="340" cy="230" r="2.5" fill="#d97706" opacity="0.4"/>
        <circle cx="980" cy="50" r="2" fill="#d97706" opacity="0.4"/>
        <circle cx="1090" cy="240" r="3" fill="#d97706" opacity="0.4"/>
        <circle cx="1180" cy="80" r="1.5" fill="#d97706" opacity="0.5"/>
        <circle cx="610" cy="40" r="1.5" fill="#d97706" opacity="0.5"/>
        <circle cx="760" cy="260" r="2" fill="#d97706" opacity="0.4"/>
      </svg>

      <!-- Content -->
      <div class="relative max-w-3xl mx-auto text-center">
        <!-- small leaf icon above text -->
        <div class="flex items-center justify-center mb-5">
          <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
        </div>
        <?php $welcome_cta = get_field( 'welcome_cta' ); ?>
        <p class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-forest-900 leading-snug mb-2">
          <?php echo wp_kses_post( get_field( 'welcome_heading' ) ?: 'We eagerly await the opportunity to welcome you to <em class="text-amber-700 not-italic">Sparsha</em>' ); ?>
        </p>
        <p class="font-serif text-xl sm:text-2xl italic text-forest-700 mb-9">
          <?php echo esc_html( get_field( 'welcome_subheading' ) ?: 'and assist you on your journey to well-being.' ); ?>
        </p>
        <a href="<?php echo esc_url( $welcome_cta['url'] ?? home_url( '/contact' ) ); ?>" class="inline-flex items-center gap-2 bg-forest-800 hover:bg-forest-900 text-amber-100 font-semibold text-xs tracking-[0.25em] uppercase px-9 py-4 rounded-full transition-all duration-300 shadow-lg shadow-forest-900/20 hover:shadow-xl hover:shadow-forest-900/30 hover:-translate-y-0.5">
          <?php echo esc_html( $welcome_cta['title'] ?? 'Book an Appointment' ); ?>
          <i class="fa-solid fa-arrow-right w-4 h-4"></i>
        </a>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════ WHY PANCHAKARMA ══════════════════════════════════════════ -->
    <section class="py-24 px-6 bg-cream">
      <div class="max-w-4xl mx-auto text-center">

        <div class="reveal mb-12">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( get_field( 'panchakarma_label' ) ?: 'Panchakarma & Overall Wellness' ); ?></p>
          <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-forest-900 leading-tight uppercase mb-2">
            <?php echo esc_html( get_field( 'panchakarma_heading' ) ?: 'Why Should One Consider Undergoing Ayurvedic Detox Therapy (Panchakarma)?' ); ?>
          </h2>
          <div class="flex items-center justify-center gap-4 mt-4">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
        </div>

        <div class="space-y-6 text-forest-600 leading-relaxed reveal [&_strong]:text-forest-800 [&_strong]:font-semibold">
          <?php echo wp_kses_post( get_field( 'panchakarma_content' ) ); ?>
        </div>

        <?php $pancha_cta = get_field( 'panchakarma_cta' ); ?>
        <div class="reveal mt-10">
          <a href="<?php echo esc_url( $pancha_cta['url'] ?? home_url( '/treatments/ayurveda-cure/panchakarma-treatment' ) ); ?>" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-amber-100 font-semibold px-8 py-3.5 rounded-full transition-colors shadow-sm">
            <?php echo esc_html( $pancha_cta['title'] ?? 'Learn About Panchakarma' ); ?>
            <i class="fa-solid fa-arrow-right w-4 h-4"></i>
          </a>
        </div>

      </div>
    </section>

    <!-- ═══════════════════════════════════════════ TESTIMONIALS (GOOGLE REVIEWS) ══════════════════════════════════════════ -->
    <section class="py-20 px-6 bg-cream">
      <div class="max-w-4xl mx-auto text-center">
        <div class="reveal mb-10">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( get_field( 'testimonials_label' ) ?: 'What Our Guests Say' ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 mb-2"><?php echo esc_html( get_field( 'testimonials_heading' ) ?: 'Testimonials' ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <div class="flex items-center justify-center text-forest-700 shrink-0">
              <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            </div>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
          <p class="text-forest-600 max-w-xl mx-auto"><?php echo esc_html( get_field( 'testimonials_description' ) ?: 'Read what our guests say about their healing experience at Sparsha Ayurveda Centre.' ); ?></p>
        </div>
        <?php
        $reviews_shortcode = trim( (string) get_field( 'testimonials_shortcode' ) );
        $reviews_link      = get_field( 'testimonials_link' ) ?: 'https://www.google.com/search?q=Sparsha+Ayurveda+Budapest+reviews';
        if ( $reviews_shortcode ) : ?>
        <div class="reveal"><?php echo do_shortcode( $reviews_shortcode ); ?></div>
        <?php else : ?>
        <!-- Fallback: placeholder card -->
        <div class="reveal bg-white rounded-3xl border border-forest-100 shadow-sm p-10 flex flex-col items-center gap-6">
          <div class="flex items-center gap-3">
            <i class="fa-brands fa-google text-3xl text-[#4285F4]"></i>
            <span class="font-semibold text-forest-900 text-lg">Google Reviews</span>
          </div>
          <p class="text-forest-500 text-sm">Our Google Reviews are displayed here — see what our guests are saying about Sparsha Ayurveda Centre.</p>
          <a href="<?php echo esc_url( $reviews_link ); ?>" target="_blank" rel="noopener noreferrer"
             class="inline-flex items-center gap-2 border border-forest-300 hover:bg-forest-700 hover:text-white hover:border-forest-700 text-forest-700 font-medium px-7 py-3 rounded-full transition-all duration-300 text-sm">
            Read Our Google Reviews
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════ INTERESTING FACTS ══════════════════════════════════════════ -->
    <section class="relative py-28 lg:py-32 overflow-hidden" style="background-color: #8d5a3a;">

      <!-- Watercolor splash edge (TOP) -->
      <svg class="absolute -top-7 left-0 w-full h-20 lg:h-28 z-10 pointer-events-none" viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <filter id="watercolor-top" x="-5%" y="-50%" width="110%" height="200%">
            <feTurbulence type="fractalNoise" baseFrequency="0.022" numOctaves="3" seed="7" />
            <feDisplacementMap in="SourceGraphic" scale="22" />
          </filter>
        </defs>
        <!-- main cream wave -->
        <path d="M0,0 L0,55 C120,82 240,30 360,52 C480,74 600,40 720,58 C840,76 960,32 1080,50 C1200,68 1320,28 1440,48 L1440,0 Z"
              fill="#fdf8f0" filter="url(#watercolor-top)"/>
        <!-- secondary lighter splash above -->
        <path d="M0,0 L0,38 C160,58 320,18 480,36 C640,54 800,22 960,40 C1120,58 1280,20 1440,34 L1440,0 Z"
              fill="#fdf8f0" opacity="0.55" filter="url(#watercolor-top)"/>
        <!-- splash droplets -->
        <circle cx="180" cy="82" r="6" fill="#fdf8f0" opacity="0.7"/>
        <circle cx="190" cy="92" r="3" fill="#fdf8f0" opacity="0.5"/>
        <circle cx="520" cy="78" r="5" fill="#fdf8f0" opacity="0.6"/>
        <circle cx="870" cy="86" r="7" fill="#fdf8f0" opacity="0.7"/>
        <circle cx="880" cy="98" r="3" fill="#fdf8f0" opacity="0.5"/>
        <circle cx="1240" cy="80" r="5" fill="#fdf8f0" opacity="0.6"/>
      </svg>

      <!-- Watercolor splash edge (BOTTOM) -->
      <svg class="absolute -bottom-7 left-0 w-full h-20 lg:h-28 z-10 pointer-events-none" viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <filter id="watercolor-bot" x="-5%" y="-50%" width="110%" height="200%">
            <feTurbulence type="fractalNoise" baseFrequency="0.022" numOctaves="3" seed="13" />
            <feDisplacementMap in="SourceGraphic" scale="22" />
          </filter>
        </defs>
        <path d="M0,120 L0,65 C120,38 240,90 360,68 C480,46 600,80 720,62 C840,44 960,88 1080,70 C1200,52 1320,92 1440,72 L1440,120 Z"
              fill="#fdf8f0" filter="url(#watercolor-bot)"/>
        <path d="M0,120 L0,82 C160,62 320,102 480,84 C640,66 800,98 960,80 C1120,62 1280,100 1440,86 L1440,120 Z"
              fill="#fdf8f0" opacity="0.55" filter="url(#watercolor-bot)"/>
        <circle cx="220" cy="38" r="6" fill="#fdf8f0" opacity="0.7"/>
        <circle cx="230" cy="26" r="3" fill="#fdf8f0" opacity="0.5"/>
        <circle cx="560" cy="42" r="5" fill="#fdf8f0" opacity="0.6"/>
        <circle cx="940" cy="36" r="7" fill="#fdf8f0" opacity="0.7"/>
        <circle cx="950" cy="22" r="3" fill="#fdf8f0" opacity="0.5"/>
        <circle cx="1280" cy="40" r="5" fill="#fdf8f0" opacity="0.6"/>
      </svg>

      <!-- Heading -->
      <div class="relative z-20 text-center px-6 mb-10 reveal">
        <p class="text-amber-300 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( sparsha_t( get_field( 'facts_label' ) ?: 'Ancient Ayurvedic Wisdom' ) ); ?></p>
        <h2 class="font-serif text-4xl lg:text-5xl font-bold text-white mb-2"><?php echo esc_html( sparsha_t( get_field( 'facts_heading' ) ?: 'Interesting Facts' ) ); ?></h2>
        <div class="flex items-center justify-center gap-4 mt-3">
          <div class="h-[1px] bg-white/30 w-24"></div>
          <div class="flex items-center justify-center text-white shrink-0">
            <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
          </div>
          <div class="h-[1px] bg-white/30 w-24"></div>
        </div>
      </div>

      <!-- Slider wrapper -->
      <div class="relative z-20 max-w-7xl mx-auto px-10 lg:px-16">

        <!-- Prev Button -->
        <button id="facts-prev" aria-label="Previous"
          class="absolute left-0 lg:left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white hover:bg-forest-900 border border-forest-100 hover:border-forest-900 flex items-center justify-center text-forest-900 hover:text-white transition-all duration-300 shadow-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        </button>

        <div class="swiper facts-swiper overflow-hidden">
          <div class="swiper-wrapper">

            <?php
            $facts = get_field( 'facts_items' );
            $fact_fallbacks = array( 'imgi_49_Detox-Your-Body-With-Ayurveda.jpg', 'imgi_46_2.jpg', 'imgi_47_3.jpg', 'imgi_48_4.jpg' );
            if ( $facts ) : foreach ( $facts as $fk => $fact ) :
              $fact_img = ( ! empty( $fact['image']['url'] ) ) ? $fact['image']['url'] : get_template_directory_uri() . '/assets/images/' . $fact_fallbacks[ $fk % count( $fact_fallbacks ) ];
              $fact_alt = ( ! empty( $fact['image']['alt'] ) ) ? $fact['image']['alt'] : $fact['heading'];
            ?>
            <div class="swiper-slide py-6 lg:py-10 px-4 lg:px-10">
              <div class="fact-card group flex flex-col lg:flex-row items-center gap-10 lg:gap-16">
                <div class="order-2 lg:order-1 lg:w-1/2 text-center lg:text-left">
                  <span class="inline-block bg-amber-500/20 text-amber-200 text-[10px] font-bold tracking-[0.3em] px-3 py-1 rounded-sm mb-5"><?php echo esc_html( sparsha_t( 'FACT' ) ); ?> &middot; <?php echo esc_html( sprintf( '%02d', $fk + 1 ) ); ?></span>
                  <h3 class="font-serif text-3xl lg:text-4xl xl:text-5xl font-bold text-white mb-5 leading-tight"><?php echo esc_html( $fact['heading'] ); ?></h3>
                  <p class="text-white/85 text-base lg:text-lg leading-relaxed"><?php echo wp_kses_post( $fact['content'] ); ?></p>
                </div>
                <div class="order-1 lg:order-2 lg:w-1/2 flex justify-center">
                  <div class="fact-polaroid relative w-full max-w-md">
                    <div class="relative aspect-[4/5] overflow-hidden">
                      <img src="<?php echo esc_url( $fact_img ); ?>" alt="<?php echo esc_attr( $fact_alt ); ?>" class="fact-photo w-full h-full object-cover">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; endif; ?>

          </div>
          <div class="swiper-pagination facts-pagination mt-8"></div>
        </div>

        <!-- Next Button -->
        <button id="facts-next" aria-label="Next"
          class="absolute right-0 lg:right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white hover:bg-forest-900 border border-forest-100 hover:border-forest-900 flex items-center justify-center text-forest-900 hover:text-white transition-all duration-300 shadow-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
        </button>

      </div>
    </section>

    <!-- ═══════════════════════════════════════════ FOUNDER ══════════════════════════════════════════ -->
    <section id="team" class="py-24 px-6 bg-cream">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">

        <div class="reveal order-2 lg:order-1">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( get_field( 'founder_label' ) ?: '✦ Trusted Experience' ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 leading-tight mb-2">
            <?php echo esc_html( get_field( 'founder_name' ) ?: 'Girish Mokeri' ); ?>
          </h2>
          <div class="flex items-center gap-4 mt-3 mb-7">
            <div class="h-[1px] bg-forest-900/20 w-20"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            <div class="h-[1px] bg-forest-900/20 w-20"></div>
          </div>
          <div class="text-forest-600 leading-relaxed space-y-5 mb-8 [&_strong]:text-forest-800 [&_strong]:font-semibold">
            <?php echo wp_kses_post( get_field( 'founder_content' ) ); ?>
          </div>

          <?php $founder_tags = get_field( 'founder_tags' ); if ( $founder_tags ) : ?>
          <div class="flex flex-wrap gap-3 mb-6">
            <?php foreach ( $founder_tags as $tag ) : ?>
            <span class="bg-forest-100 text-forest-700 text-sm font-medium px-4 py-2 rounded-full"><?php echo esc_html( $tag['text'] ); ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php $founder_cta = get_field( 'founder_cta' ); ?>
          <a href="<?php echo esc_url( $founder_cta['url'] ?? home_url( '/contact' ) ); ?>" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-amber-100 font-semibold px-7 py-3.5 rounded-full transition-colors shadow-sm">
            <?php echo esc_html( $founder_cta['title'] ?? 'Take the Dosha Test' ); ?>
            <i class="fa-solid fa-arrow-right w-4 h-4"></i>
          </a>
        </div>

        <div class="reveal order-1 lg:order-2 relative max-w-md mx-auto w-full">

          <!-- Decorative circle outline behind -->
          <svg class="absolute -top-6 -right-6 w-44 h-44 lg:w-56 lg:h-56 opacity-40 pointer-events-none" viewBox="0 0 200 200">
            <circle cx="100" cy="100" r="98" fill="none" stroke="#347319" stroke-width="0.8" stroke-dasharray="2 4"/>
            <circle cx="100" cy="100" r="80" fill="none" stroke="#d97706" stroke-width="0.6"/>
          </svg>

          <!-- Trailing leaves (top-right) -->
          <svg class="absolute -top-8 -right-2 w-28 lg:w-36 opacity-70 pointer-events-none" viewBox="0 0 120 140" fill="none">
            <path d="M60 130 C 55 100 50 70 60 30 C 65 18 60 8 60 2" stroke="#285a14" stroke-width="1.4" stroke-linecap="round"/>
            <path d="M60 120 C 35 115 18 95 14 75 C 35 80 53 95 60 120Z" fill="#479228" fill-opacity="0.45" stroke="#285a14" stroke-width="0.8"/>
            <path d="M60 85 C 38 82 23 65 22 48 C 40 53 55 68 60 85Z" fill="#479228" fill-opacity="0.45" stroke="#285a14" stroke-width="0.8"/>
            <path d="M60 50 C 48 48 38 36 36 24 C 50 28 58 38 60 50Z" fill="#479228" fill-opacity="0.45" stroke="#285a14" stroke-width="0.8"/>
            <path d="M60 100 C 82 95 96 75 100 55 C 82 62 67 78 60 100Z" fill="#347319" fill-opacity="0.4" stroke="#285a14" stroke-width="0.8"/>
            <path d="M60 65 C 76 60 88 46 92 30 C 78 36 64 50 60 65Z" fill="#347319" fill-opacity="0.4" stroke="#285a14" stroke-width="0.8"/>
          </svg>

          <!-- Arch-shaped image (the focal element) -->
          <div class="relative aspect-[3/4]"
               style="border-top-left-radius: 9999px; border-top-right-radius: 9999px; overflow: hidden;
                      box-shadow: 0 25px 50px -12px rgba(40,90,20,0.35), 0 0 0 8px #fdf8f0, 0 0 0 9px #c4b89a;">
            <img src="<?php echo esc_url( $founder_img_url ); ?>" alt="<?php echo esc_attr( $founder_img_alt ); ?>" class="w-full h-full object-cover">
          </div>

          <!-- Floating "12+ Years" badge -->
          <div class="absolute -top-4 -left-4 lg:-left-8 bg-amber-500 text-bark rounded-full w-24 h-24 flex flex-col items-center justify-center shadow-xl shadow-amber-900/30 rotate-[-8deg] z-10">
            <p class="font-serif font-bold text-3xl leading-none">12<span class="text-lg align-super">+</span></p>
            <p class="text-[9px] font-semibold tracking-[0.1em] uppercase mt-0.5"><?php echo esc_html( sparsha_t( 'Years' ) ); ?></p>
          </div>

          <!-- Floating signature/quote card (Overlapping style) -->
          <div class="absolute -bottom-6 -right-8 lg:-right-16 bg-forest-800 text-white rounded-2xl p-5 max-w-[240px] shadow-xl rotate-[3deg] z-20">
            <svg class="w-6 h-6 text-amber-400 mb-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M9 7H5C4 7 3 8 3 9V13C3 14 4 15 5 15H7C8 15 9 14 9 13V9M9 13C9 16 7 17 5 17M19 7H15C14 7 13 8 13 9V13C13 14 14 15 15 15H17C18 15 19 14 19 13V9M19 13C19 16 17 17 15 17"/>
            </svg>
            <p class="text-sm italic leading-relaxed text-forest-100"><?php echo esc_html( sparsha_t( get_field( 'founder_quote' ) ?: 'Nature has no side effects — that is Ayurveda\'s greatest gift.' ) ); ?></p>
          </div>

        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════ JOURNAL ══════════════════════════════════════════ -->
    <section id="blog" class="relative py-28 px-6 bg-cream overflow-hidden">

      <!-- soft scribble background -->
      <svg class="absolute -top-10 -right-20 w-72 opacity-[0.06] pointer-events-none" viewBox="0 0 200 200" fill="none" stroke="#1a3a10" stroke-width="0.5">
        <path d="M10 80 Q60 20 100 60 T190 30" stroke-dasharray="3 5"/>
        <path d="M30 130 Q80 80 130 120 T200 100"/>
        <circle cx="160" cy="40" r="20" stroke-dasharray="2 4"/>
      </svg>

      <div class="relative max-w-7xl mx-auto">
        <!-- Section header -->
        <div class="text-center mb-20 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( sparsha_t( get_field( 'journal_label' ) ?: 'Ayurvedic Journal' ) ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 mb-2"><?php echo esc_html( sparsha_t( get_field( 'journal_heading' ) ?: 'Latest Articles' ) ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
          <p class="text-forest-600 max-w-md mx-auto"><?php echo esc_html( sparsha_t( get_field( 'journal_description' ) ?: 'Hand-picked reads on Ayurveda, healing, and the art of living well.' ) ); ?></p>
        </div>

        <!-- Cards grid (latest posts, staggered) -->
        <?php
        $journal_count = absint( get_field( 'journal_posts_count' ) ) ?: 3;
        $journal_q = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => $journal_count, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) );
        $j_styles = array(
          array( 'wrap' => 'lg:-translate-y-4 lg:rotate-[-1.5deg] hover:-translate-y-6', 'tape_pos' => 'left-8', 'tape_rot' => 'rotate-[-3deg]', 'tape_bg' => 'rgba(217,119,6,0.55), rgba(217,119,6,0.55) 3px, rgba(217,119,6,0.35) 3px, rgba(217,119,6,0.35) 6px', 'stamp_rot' => 'rotate-[8deg]', 'cat' => 'bg-forest-700 text-cream' ),
          array( 'wrap' => 'lg:translate-y-6 lg:rotate-[1deg] hover:-translate-y-2', 'tape_pos' => 'right-6', 'tape_rot' => 'rotate-[5deg]', 'tape_bg' => 'rgba(58,90,20,0.55), rgba(58,90,20,0.55) 3px, rgba(58,90,20,0.35) 3px, rgba(58,90,20,0.35) 6px', 'stamp_rot' => 'rotate-[-6deg]', 'cat' => 'bg-amber-600 text-white' ),
          array( 'wrap' => 'lg:-translate-y-2 lg:rotate-[1.5deg] hover:-translate-y-6', 'tape_pos' => 'left-10', 'tape_rot' => 'rotate-[-4deg]', 'tape_bg' => 'rgba(217,119,6,0.55), rgba(217,119,6,0.55) 3px, rgba(217,119,6,0.35) 3px, rgba(217,119,6,0.35) 6px', 'stamp_rot' => 'rotate-[10deg]', 'cat' => 'bg-forest-900 text-amber-300' ),
        );
        $j_fallbacks = array( 'imgi_49_Detox-Your-Body-With-Ayurveda.jpg', 'imgi_46_2.jpg', 'imgi_47_3.jpg' );
        if ( $journal_q->have_posts() ) :
        ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-10 lg:gap-8">
          <?php $ji = 0; while ( $journal_q->have_posts() ) : $journal_q->the_post();
            $st = $j_styles[ $ji % 3 ];
            $cats = get_the_category(); $cat_name = ! empty( $cats ) ? $cats[0]->name : 'Ayurveda';
            $read = max( 1, ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
          ?>
          <article class="reveal group relative <?php echo esc_attr( $st['wrap'] ); ?> hover:rotate-0 transition-all duration-500">
            <a href="<?php the_permalink(); ?>" class="block">
              <span class="absolute -top-3 <?php echo esc_attr( $st['tape_pos'] ); ?> w-20 h-6 bg-amber-300/70 <?php echo esc_attr( $st['tape_rot'] ); ?> z-20 shadow-sm" style="background: repeating-linear-gradient(45deg, <?php echo esc_attr( $st['tape_bg'] ); ?>);"></span>
              <div class="bg-white rounded-sm overflow-hidden shadow-[0_10px_30px_-12px_rgba(0,0,0,0.18)] group-hover:shadow-[0_20px_40px_-12px_rgba(0,0,0,0.28)] transition-shadow border-t border-l border-forest-100">
                <div class="relative h-56 overflow-hidden">
                  <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'sparsha-card', array( 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-700' ) ); ?>
                  <?php else :
                    $j_default = get_field( 'single_post_default_image', 'option' );
                    $j_url = is_array( $j_default ) ? ( $j_default['sizes']['medium_large'] ?? $j_default['url'] ) : sparsha_page_banner_url();
                    if ( $j_url ) : ?>
                    <img src="<?php echo esc_url( $j_url ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <?php else : ?>
                    <div class="w-full h-full bg-forest-100"></div>
                    <?php endif; ?>
                  <?php endif; ?>
                  <span class="absolute top-4 right-4 w-14 h-14 rounded-full bg-cream/95 border-2 border-amber-500 font-serif font-bold text-amber-700 text-xl flex items-center justify-center <?php echo esc_attr( $st['stamp_rot'] ); ?> shadow-md">&#8470;<?php echo esc_html( $ji + 1 ); ?></span>
                </div>
                <div class="p-7">
                  <div class="flex flex-wrap items-center gap-2 mb-3 text-[10px] font-bold tracking-wider uppercase">
                    <span class="<?php echo esc_attr( $st['cat'] ); ?> px-2 py-1 rounded-sm"><?php echo esc_html( sparsha_t( $cat_name ) ); ?></span>
                    <span class="text-forest-500 whitespace-nowrap">&middot; <?php echo esc_html( $read ); ?> <?php echo esc_html( sparsha_t( 'min read' ) ); ?></span>
                  </div>
                  <h3 class="font-serif text-xl lg:text-2xl font-bold text-forest-900 mb-4 leading-snug group-hover:text-amber-700 transition-colors"><?php the_title(); ?></h3>
                  <p class="text-forest-600 text-sm leading-relaxed mb-5"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                  <span class="inline-flex items-center gap-2 text-amber-600 font-semibold text-sm">
                    <?php echo esc_html( sparsha_label( 'label_read_more', 'Read More' ) ); ?>
                    <i class="fa-solid fa-arrow-right w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                  </span>
                </div>
              </div>
            </a>
          </article>
          <?php $ji++; endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>

        <!-- "Browse all" link -->
        <?php $blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog' ); ?>
        <div class="text-center mt-20 reveal">
          <a href="<?php echo esc_url( $blog_url ); ?>" class="inline-flex items-center gap-3 text-forest-700 font-semibold hover:text-amber-600 transition-colors group">
            <span class="h-px w-8 bg-forest-300 group-hover:bg-amber-500 transition-colors"></span>
            <?php echo esc_html( sparsha_label( 'label_view_all', 'View All' ) ); ?>
            <span class="h-px w-8 bg-forest-300 group-hover:bg-amber-500 transition-colors"></span>
          </a>
        </div>
      </div>
    </section>

  </main>

<?php get_footer(); ?>
