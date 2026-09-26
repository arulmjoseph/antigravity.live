<?php
/*
 * Template Name: Contact
 * Template Post Type: page
 */
get_header();
$uri = get_template_directory_uri();
$hero_image   = get_field( 'hero_image' );
$hero_img_url = $hero_image ? $hero_image['url'] : $uri . '/assets/images/imgi_49_Detox-Your-Body-With-Ayurveda.jpg';
$hero_img_alt = $hero_image ? $hero_image['alt'] : 'Contact Sparsha Ayurveda';
?>

  <!-- Hero -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $hero_img_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( sparsha_t( 'Get in' ) ); ?> <em class="text-amber-300 not-italic"><?php echo esc_html( sparsha_t( 'Touch' ) ); ?></em></h1>
    </div>
  </section>

  <main>

    <!-- Details Grid -->
    <section class="py-20 px-6 bg-cream">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-3 gap-10">

        <!-- Info cards -->
        <div class="space-y-5 reveal">
          <div class="bg-white rounded-2xl p-6 border border-forest-100 shadow-sm flex items-start gap-4 group hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-forest-100 group-hover:bg-forest-700 flex items-center justify-center flex-shrink-0 transition-colors"><i class="fa-solid fa-location-dot w-5 h-5 text-forest-700 group-hover:text-amber-300 transition-colors"></i></div>
            <div><p class="font-semibold text-forest-900 mb-1"><?php echo esc_html( sparsha_t( 'Visit Us' ) ); ?></p><p class="text-forest-600 text-sm leading-relaxed"><?php echo nl2br( esc_html( sparsha_t( get_theme_mod( 'sparsha_address', 'Bd. Pipera nr. 1-VIII D, Voluntari, Romania' ) ) ) ); ?></p></div>
          </div>
          <div class="bg-white rounded-2xl p-6 border border-forest-100 shadow-sm flex items-start gap-4 group hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-forest-100 group-hover:bg-forest-700 flex items-center justify-center flex-shrink-0 transition-colors"><i class="fa-solid fa-phone w-5 h-5 text-forest-700 group-hover:text-amber-300 transition-colors"></i></div>
            <div><p class="font-semibold text-forest-900 mb-1"><?php echo esc_html( sparsha_t( 'Call Us' ) ); ?></p>
              <a href="tel:<?php echo esc_attr( get_theme_mod( 'sparsha_phone_href', '+36705625113' ) ); ?>" class="text-forest-600 text-sm hover:text-amber-600 block transition-colors"><?php echo esc_html( get_theme_mod( 'sparsha_phone', '+36 70 562 5113' ) ); ?></a>
              <a href="tel:<?php echo esc_attr( get_theme_mod( 'sparsha_phone_2_href', '+36707428192' ) ); ?>" class="text-forest-600 text-sm hover:text-amber-600 block transition-colors"><?php echo esc_html( get_theme_mod( 'sparsha_phone_2', '+36 70 742 8192' ) ); ?></a>
            </div>
          </div>
          <?php $wa_num = get_theme_mod( 'sparsha_whatsapp', '' ); $wa_href = preg_replace( '/\D/', '', get_theme_mod( 'sparsha_whatsapp_href', '' ) ); if ( $wa_href ) : ?>
          <div class="bg-white rounded-2xl p-6 border border-forest-100 shadow-sm flex items-start gap-4 group hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-forest-100 group-hover:bg-[#25D366] flex items-center justify-center flex-shrink-0 transition-colors"><i class="fa-brands fa-whatsapp text-xl text-[#25D366] group-hover:text-white transition-colors"></i></div>
            <div><p class="font-semibold text-forest-900 mb-1"><?php echo esc_html( sparsha_t( 'WhatsApp' ) ); ?></p>
              <a href="https://wa.me/<?php echo esc_attr( $wa_href ); ?>" target="_blank" rel="noopener" class="text-forest-600 text-sm hover:text-amber-600 transition-colors"><?php echo esc_html( $wa_num ?: $wa_href ); ?></a>
            </div>
          </div>
          <?php endif; ?>
          <?php $email = get_theme_mod( 'sparsha_email', '' ); if ( $email ) : ?>
          <div class="bg-white rounded-2xl p-6 border border-forest-100 shadow-sm flex items-start gap-4 group hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-forest-100 group-hover:bg-forest-700 flex items-center justify-center flex-shrink-0 transition-colors"><i class="fa-solid fa-envelope w-5 h-5 text-forest-700 group-hover:text-amber-300 transition-colors"></i></div>
            <div><p class="font-semibold text-forest-900 mb-1"><?php echo esc_html( sparsha_t( 'Email Us' ) ); ?></p><a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-forest-600 text-sm hover:text-amber-600 transition-colors"><?php echo esc_html( $email ); ?></a></div>
          </div>
          <?php endif; ?>
          <!-- Hours -->
          <div class="bg-forest-900 rounded-2xl p-6 text-white">
            <p class="font-semibold mb-4 text-amber-400 text-xs tracking-widest uppercase"><?php echo esc_html( sparsha_t( 'Opening Hours' ) ); ?></p>
            <ul class="space-y-2 text-sm">
              <li class="flex justify-between"><span class="text-forest-300"><?php echo esc_html( sparsha_t( 'Mon – Fri' ) ); ?></span><span class="font-medium"><?php echo esc_html( get_theme_mod( 'sparsha_hours_weekday', '10:00 – 20:00' ) ); ?></span></li>
              <li class="flex justify-between"><span class="text-forest-300"><?php echo esc_html( sparsha_t( 'Saturday' ) ); ?></span><span class="font-medium"><?php echo esc_html( get_theme_mod( 'sparsha_hours_sat', '10:00 – 18:00' ) ); ?></span></li>
              <li class="flex justify-between"><span class="text-forest-300"><?php echo esc_html( sparsha_t( 'Sunday' ) ); ?></span><span class="text-forest-400 italic"><?php echo esc_html( sparsha_t( get_theme_mod( 'sparsha_hours_sun', 'By appointment' ) ) ); ?></span></li>
            </ul>
          </div>
          <!-- Social -->
          <div class="bg-white rounded-2xl p-6 border border-forest-100 shadow-sm">
            <p class="font-semibold text-forest-900 mb-4"><?php echo esc_html( sparsha_t( 'Follow Us' ) ); ?></p>
            <div class="flex gap-3">
              <?php $fb = get_theme_mod( 'sparsha_facebook', '' ); if ( $fb ) : ?>
              <a href="<?php echo esc_url( $fb ); ?>" aria-label="Facebook" class="w-10 h-10 rounded-full bg-forest-100 hover:bg-forest-700 hover:text-white flex items-center justify-center text-forest-600 transition-colors" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f w-4 h-4"></i></a>
              <?php endif; ?>
              <?php $ig = get_theme_mod( 'sparsha_instagram', '' ); if ( $ig ) : ?>
              <a href="<?php echo esc_url( $ig ); ?>" aria-label="Instagram" class="w-10 h-10 rounded-full bg-forest-100 hover:bg-forest-700 hover:text-white flex items-center justify-center text-forest-600 transition-colors" target="_blank" rel="noopener"><i class="fa-brands fa-instagram w-4 h-4"></i></a>
              <?php endif; ?>
              <?php $yt = get_theme_mod( 'sparsha_youtube', '' ); if ( $yt ) : ?>
              <a href="<?php echo esc_url( $yt ); ?>" aria-label="YouTube" class="w-10 h-10 rounded-full bg-forest-100 hover:bg-forest-700 hover:text-white flex items-center justify-center text-forest-600 transition-colors" target="_blank" rel="noopener"><i class="fa-brands fa-youtube w-4 h-4"></i></a>
              <?php endif; ?>
              <?php $tt = get_theme_mod( 'sparsha_tiktok', '#' ); if ( $tt ) : ?>
              <a href="<?php echo esc_url( $tt ); ?>" aria-label="TikTok" class="w-10 h-10 rounded-full bg-forest-100 hover:bg-forest-700 hover:text-white flex items-center justify-center text-forest-600 transition-colors" target="_blank" rel="noopener"><i class="fa-brands fa-tiktok w-4 h-4"></i></a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Booking form -->
        <div class="lg:col-span-2 reveal space-y-6">

          <!-- CF7 form -->
          <div class="bg-white rounded-3xl p-8 shadow-sm border border-forest-100">
            <h2 class="font-serif text-3xl font-bold text-forest-900 mb-2"><?php echo esc_html( sparsha_t( get_field( 'form_heading' ) ?: 'Book Your Appointment' ) ); ?></h2>
            <?php $form_desc = get_field( 'form_description' ); if ( $form_desc ) : ?>
            <p class="text-forest-500 text-sm mb-4"><?php echo esc_html( $form_desc ); ?></p>
            <?php endif; ?>
            <?php $booking_intro = get_field( 'booking_intro' ); if ( $booking_intro ) : ?>
            <div class="text-forest-600 text-sm leading-relaxed mb-8 space-y-2"><?php echo wp_kses_post( $booking_intro ); ?></div>
            <?php endif; ?>

            <?php
            $shortcode = get_field( 'contact_form_shortcode' );
            if ( $shortcode ) :
            ?>
            <div class="sparsha-cf7"><?php echo do_shortcode( wp_kses_post( $shortcode ) ); ?></div>
            <?php else : ?>
            <p class="text-forest-500 text-sm"><?php echo esc_html( sparsha_t( 'Booking form coming soon. Add the Contact Form 7 shortcode in this page’s editor.' ) ); ?></p>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </section>

    <?php
    $map_enable = get_field( 'map_enable' );
    if ( $map_enable !== false ) :
      $map_input = trim( (string) ( get_field( 'map_embed' ) ?: get_field( 'map_url' ) ) );
      if ( ! $map_input ) {
        $map_input = 'https://maps.google.com/maps?q=Bd.+Pipera+nr.+1-VIII+D,+Voluntari,+Romania&t=&z=15&ie=UTF8&iwloc=&output=embed';
      }

      $map_src = '';
      if ( strpos( $map_input, '<iframe' ) !== false ) {
        if ( preg_match( '/src=["\']([^"\']+)["\']/', $map_input, $m ) ) {
          $map_src = $m[1];
        }
      } elseif ( strpos( $map_input, 'maps.app.goo.gl' ) !== false || strpos( $map_input, 'goo.gl/maps' ) !== false ) {
        $map_src = 'https://maps.google.com/maps?q=Bd.+Pipera+nr.+1-VIII+D,+Voluntari,+Romania&t=&z=15&ie=UTF8&iwloc=&output=embed';
      } else {
        $map_src = $map_input;
      }
    ?>
    <!-- Map (Full Width) -->
    <section class="w-full pb-0 bg-cream">
      <div class="w-full reveal">
        <div class="w-full overflow-hidden">
          <iframe src="<?php echo esc_url( $map_src ); ?>" width="100%" height="500" style="border:0;display:block;" allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin" title="<?php esc_attr_e( 'Sparsha Ayurveda Centre — Google Map', 'sparsha-wp' ); ?>"></iframe>
        </div>
      </div>
    </section>
    <?php endif; ?>

  </main>

<?php get_footer(); ?>
