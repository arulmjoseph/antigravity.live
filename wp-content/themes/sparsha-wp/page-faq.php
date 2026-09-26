<?php
/*
 * Template Name: FAQ
 * Template Post Type: page
 */
get_header();
$uri          = get_template_directory_uri();
$hero_image   = get_field( 'hero_image' );
$hero_img_url = $hero_image ? $hero_image['url'] : $uri . '/assets/images/imgi_49_Detox-Your-Body-With-Ayurveda.jpg';
$hero_img_alt = $hero_image ? $hero_image['alt'] : 'Frequently Asked Questions';
$faqs         = get_field( 'faq_items' );
?>

  <!-- Hero -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $hero_img_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( sparsha_t( get_field( 'faq_heading' ) ?: 'Frequently Asked Questions' ) ); ?></h1>
    </div>
  </section>

  <main>
    <section class="py-24 px-6 bg-forest-50">
      <div class="max-w-3xl mx-auto">

        <div class="text-center mb-12 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( sparsha_t( get_field( 'faq_label' ) ?: 'Common Questions' ) ); ?></p>
          <h2 class="font-serif text-4xl font-bold text-forest-900"><?php echo esc_html( sparsha_t( get_field( 'faq_subheading' ) ?: 'Everything You Need to Know' ) ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-4">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
        </div>

        <?php if ( $faqs ) : ?>
        <div class="space-y-4">
          <?php foreach ( $faqs as $faq ) : ?>
          <details class="reveal bg-white rounded-2xl border border-forest-100 shadow-sm group">
            <summary class="flex items-center justify-between p-6 cursor-pointer font-semibold text-forest-900 list-none">
              <?php echo esc_html( $faq['question'] ); ?>
              <i class="fa-solid fa-chevron-down w-5 h-5 text-forest-500 group-open:rotate-180 transition-transform flex-shrink-0 ml-4"></i>
            </summary>
            <div class="px-6 pb-6 text-forest-600 text-sm leading-relaxed"><?php echo wp_kses_post( wpautop( $faq['answer'] ) ); ?></div>
          </details>
          <?php endforeach; ?>
        </div>
        <?php else : ?>
        <p class="text-center text-forest-500"><?php esc_html_e( 'FAQs coming soon. Add questions in the FAQ page editor.', 'sparsha-wp' ); ?></p>
        <?php endif; ?>

        <!-- Still have questions CTA -->
        <div class="reveal mt-14 text-center bg-forest-900 rounded-3xl p-10">
          <h3 class="font-serif text-2xl font-bold text-white mb-2"><?php echo esc_html( sparsha_t( 'Still have questions?' ) ); ?></h3>
          <p class="text-forest-300 text-sm mb-6"><?php echo esc_html( sparsha_t( 'Our specialists are happy to help — reach out and we\'ll get back to you within 24 hours.' ) ); ?></p>
          <a href="<?php echo esc_url( function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? home_url( '/ro/contactati-ne/' ) : home_url( '/contact/' ) ); ?>" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-bark font-semibold px-7 py-3.5 rounded-full transition-colors shadow-lg">
            <?php echo esc_html( sparsha_t( 'Contact Us' ) ); ?>
            <i class="fa-solid fa-arrow-right w-4 h-4"></i>
          </a>
        </div>

      </div>
    </section>
  </main>

<?php get_footer(); ?>
