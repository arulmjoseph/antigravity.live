<?php
/**
 * Template Name: Authentic Panchakarma Retreat
 * Template Post Type: page
 */

get_header();
$uri = get_template_directory_uri();

// Hero Banner & Slider
$pancha_slides = get_field( 'pancha_hero_slides' );
$hero_img_arr  = get_field( 'pancha_hero_image' );
$hero_img_url  = is_array( $hero_img_arr ) ? ( $hero_img_arr['url'] ?? '' ) : sparsha_page_banner_url();
if ( ! $hero_img_url ) {
	$hero_img_url = $uri . '/assets/images/imgi_49_Detox-Your-Body-With-Ayurveda.jpg';
}
$hero_title = get_field( 'pancha_hero_title' ) ?: 'Authentic Panchakarma Retreat';

if ( empty( $pancha_slides ) || ! is_array( $pancha_slides ) ) {
	$pancha_slides = array(
		array(
			'image' => array( 'url' => $hero_img_url ),
			'title' => $hero_title,
			'highlight' => '',
			'highlight_color' => '#fcd34d',
			'subtitle' => '',
		)
	);
}

// Intro section image & content
$intro_img_arr = get_field( 'pancha_intro_image' );
$intro_img_url = is_array( $intro_img_arr ) ? ( $intro_img_arr['url'] ?? '' ) : '';
if ( ! $intro_img_url ) {
	$intro_img_url = $hero_img_url;
}

$intro_desc = get_field( 'pancha_intro_description' );
if ( ! $intro_desc ) {
	$intro_desc = get_field( 'pancha_hero_description' );
}
if ( ! $intro_desc ) {
	$intro_desc = 'Sparsha is a Sanskrit word meaning "the profound healing touch"—the guiding philosophy behind every therapy we offer. Situated in Voluntari, Romania, Sparsha Ayurveda provides a peaceful wellness sanctuary where traditional Indian healing meets contemporary luxury. Using authentic equipment imported directly from Kerala, India, and guided by experienced specialists, our retreat offers a complete physical, mental, and spiritual reset.';
}
?>

<main class="bg-cream text-forest-900 min-h-screen font-sans">

  <!-- Hero Slider Section -->
  <section class="relative bg-forest-900 text-white overflow-hidden">
    <div class="swiper hero-swiper pancha-hero-swiper">
      <div class="swiper-wrapper">
        <?php foreach ( $pancha_slides as $sidx => $slide ) :
          $slide_img_url = is_array( $slide['image'] ?? null ) ? ( $slide['image']['url'] ?? '' ) : ( is_string( $slide['image'] ?? null ) ? $slide['image'] : '' );
          if ( ! $slide_img_url ) {
            $slide_img_url = $hero_img_url;
          }
          $stitle     = $slide['title'] ?? $hero_title;
          $shighlight = $slide['highlight'] ?? '';
          $shl_color  = $slide['highlight_color'] ?? '#fcd34d';
          $ssubtitle  = $slide['subtitle'] ?? '';
          $scta1      = $slide['cta_primary'] ?? null;
        ?>
        <div class="swiper-slide relative">
          <img src="<?php echo esc_url( $slide_img_url ); ?>" alt="<?php echo esc_attr( $stitle ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
          <!-- Top header background gradient (fades to bottom) -->
          <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
          <!-- Soft bottom text backdrop -->
          <div class="absolute bottom-0 inset-x-0 h-52 sm:h-64 bg-gradient-to-t from-forest-950/90 via-forest-950/45 to-transparent pointer-events-none z-10"></div>
          <!-- Bottom content container -->
          <div class="relative z-20 h-full w-full flex items-end justify-center text-center px-4 sm:px-6 pb-16 sm:pb-20 lg:pb-24">
            <div class="max-w-5xl mx-auto w-full">
              <?php if ( $ssubtitle ) : ?>
              <p class="slide-label text-amber-400 text-xs sm:text-sm font-semibold tracking-[0.2em] uppercase mb-2 sm:mb-3"><?php echo esc_html( $ssubtitle ); ?></p>
              <?php endif; ?>
              <h1 class="slide-heading font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-tight drop-shadow-sm mb-3 sm:mb-4">
                <?php echo esc_html( $stitle ); ?>
                <?php if ( $shighlight ) : ?>
                  <br><em class="not-italic" style="color: <?php echo esc_attr( $shl_color ); ?>;"><?php echo esc_html( $shighlight ); ?></em>
                <?php endif; ?>
              </h1>
              <?php if ( ! empty( $scta1 ) && is_array( $scta1 ) ) : ?>
              <div class="slide-cta flex justify-center mt-2 sm:mt-4">
                <a href="<?php echo esc_url( $scta1['url'] ); ?>" class="inline-block bg-amber-500 hover:bg-amber-400 text-bark font-semibold px-7 sm:px-8 py-2.5 sm:py-3.5 rounded-full transition-colors shadow-lg text-xs sm:text-sm"><?php echo esc_html( $scta1['title'] ); ?></a>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div><!-- /swiper-wrapper -->

      <?php if ( count( $pancha_slides ) > 1 ) : ?>
      <!-- Pagination dots -->
      <div class="swiper-pagination"></div>
      <!-- Nav arrows -->
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Intro Section (Left Image, Right Content) -->
  <?php if ( $intro_desc ) : ?>
  <section class="py-16 px-6 bg-white border-b border-forest-100">
    <div class="max-w-7xl mx-auto">
      <div class="grid lg:grid-cols-12 gap-12 items-center">
        <!-- Left Image -->
        <div class="lg:col-span-5">
          <div class="rounded-2xl overflow-hidden shadow-lg border border-forest-100 aspect-[4/3] sm:aspect-[4/3] lg:aspect-[1/1] bg-forest-50">
            <img src="<?php echo esc_url( $intro_img_url ); ?>" alt="<?php echo esc_attr( $hero_title ); ?>" class="w-full h-full object-cover">
          </div>
        </div>
        <!-- Right Content -->
        <div class="lg:col-span-7 font-light text-forest-800 text-lg sm:text-xl leading-relaxed">
          <?php echo wp_kses_post( $intro_desc ); ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Facilities & Accommodations -->
  <?php
  $fac_heading    = get_field( 'pancha_facilities_heading' ) ?: 'Our Facilities & Accommodations';
  $fac_subheading = get_field( 'pancha_facilities_subheading' ) ?: 'Your retreat is designed to offer maximum comfort and complete peace of mind, allowing you to focus entirely on your healing:';
  $fac_items      = get_field( 'pancha_facilities_items' );
  ?>
  <section class="py-16 px-6 bg-cream border-b border-forest-100">
    <div class="max-w-6xl mx-auto">
      <div class="max-w-3xl mb-12">
        <h2 class="font-serif text-3xl sm:text-4xl font-bold text-forest-900 mb-4"><?php echo esc_html( $fac_heading ); ?></h2>
        <p class="text-forest-700 text-lg"><?php echo esc_html( $fac_subheading ); ?></p>
      </div>

      <?php if ( ! empty( $fac_items ) && is_array( $fac_items ) ) : ?>
      <div class="grid md:grid-cols-3 gap-8">
        <?php foreach ( $fac_items as $idx => $item ) :
          $img_url = is_array( $item['image'] ?? null ) ? ( $item['image']['sizes']['medium_large'] ?? $item['image']['url'] ) : ( is_numeric( $item['image'] ?? null ) ? wp_get_attachment_image_url( $item['image'], 'medium_large' ) : '' );
          if ( ! $img_url ) {
            $fallback_images = array(
              $uri . '/assets/images/imgi_46_2.jpg',
              $uri . '/assets/images/imgi_47_3.jpg',
              $uri . '/assets/images/imgi_48_4.jpg',
            );
            $img_url = $fallback_images[ $idx % count( $fallback_images ) ];
          }
        ?>
        <div class="bg-white rounded-2xl border border-forest-100 shadow-sm flex flex-col justify-between overflow-hidden">
          <div>
            <?php if ( $img_url ) : ?>
            <div class="aspect-[16/10] overflow-hidden bg-forest-50 border-b border-forest-100">
              <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $item['title'] ?? '' ); ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
            </div>
            <?php endif; ?>
            <div class="p-8">
              <h3 class="font-serif text-xl font-bold text-forest-900 mb-3"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
              <p class="text-forest-700 text-base leading-relaxed"><?php echo esc_html( $item['description'] ?? '' ); ?></p>
            </div>
          </div>
          <?php if ( ! empty( $item['note'] ) ) : ?>
          <div class="px-8 pb-8">
            <p class="pt-4 border-t border-forest-100 text-xs text-forest-500 italic"><?php echo esc_html( $item['note'] ); ?></p>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- The Panchakarma Healing Journey -->
  <?php
  $jrn_heading  = get_field( 'pancha_journey_heading' ) ?: 'The Panchakarma Healing Journey';
  $jrn_question = get_field( 'pancha_journey_question' ) ?: 'Why Should One Consider Undergoing Ayurvedic Detox Therapy (Panchakarma)?';
  $jrn_content  = get_field( 'pancha_journey_content' );
  $jrn_dtitle   = get_field( 'pancha_dosha_title' ) ?: "Here's how the doshas are relevant in Panchakarma treatment";
  $jrn_dsteps   = get_field( 'pancha_dosha_steps' );
  $jrn_quote    = get_field( 'pancha_journey_quote' );
  $jrn_img_arr  = get_field( 'pancha_journey_image' );
  $jrn_img_url  = is_array( $jrn_img_arr ) ? ( $jrn_img_arr['sizes']['large'] ?? $jrn_img_arr['url'] ) : '';
  if ( ! $jrn_img_url ) {
  	$jrn_img_url = $uri . '/assets/images/imgi_48_4.jpg';
  }
  ?>
  <section class="py-16 px-6 bg-white border-b border-forest-100">
    <div class="max-w-7xl mx-auto">
      <div class="grid lg:grid-cols-12 gap-12 items-start">
        
        <!-- Left Image Column -->
        <div class="lg:col-span-5 lg:sticky lg:top-28">
          <div class="rounded-2xl overflow-hidden shadow-lg border border-forest-100 aspect-[4/5] bg-forest-50">
            <img src="<?php echo esc_url( $jrn_img_url ); ?>" alt="<?php echo esc_attr( $jrn_heading ); ?>" class="w-full h-full object-cover">
          </div>
        </div>

        <!-- Right Content Column -->
        <div class="lg:col-span-7">
          <h2 class="font-serif text-3xl sm:text-4xl font-bold text-forest-900 mb-3"><?php echo esc_html( $jrn_heading ); ?></h2>
          <h3 class="font-serif text-xl text-forest-700 italic mb-8"><?php echo esc_html( $jrn_question ); ?></h3>

          <div class="prose prose-forest max-w-none text-forest-800 text-base leading-relaxed space-y-4 mb-10">
            <?php echo wp_kses_post( $jrn_content ); ?>
          </div>

          <?php if ( ! empty( $jrn_dsteps ) && is_array( $jrn_dsteps ) ) : ?>
          <div class="my-8 p-8 rounded-2xl bg-cream border border-forest-200">
            <h4 class="font-serif text-xl font-bold text-forest-900 mb-6"><?php echo esc_html( $jrn_dtitle ); ?></h4>
            <ol class="space-y-4 list-decimal list-inside text-forest-800 leading-relaxed">
              <?php foreach ( $jrn_dsteps as $step ) : ?>
              <li class="pl-2">
                <strong class="font-bold text-forest-950"><?php echo esc_html( $step['title'] ?? '' ); ?>:</strong>
                <span><?php echo esc_html( $step['description'] ?? '' ); ?></span>
              </li>
              <?php endforeach; ?>
            </ol>
          </div>
          <?php endif; ?>

          <?php if ( ! empty( $jrn_quote ) ) : ?>
          <blockquote class="p-6 border-l-4 border-forest-800 bg-forest-50 text-forest-900 rounded-r-xl italic font-serif text-lg leading-relaxed">
            "<?php echo esc_html( $jrn_quote ); ?>"
          </blockquote>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </section>

  <!-- Our Program -->
  <?php
  $prg_heading = get_field( 'pancha_program_heading' ) ?: 'Our Program';
  $prg_items   = get_field( 'pancha_program_items' );
  ?>
  <section class="py-16 px-6 bg-cream border-b border-forest-100">
    <div class="max-w-6xl mx-auto">
      <h2 class="font-serif text-3xl sm:text-4xl font-bold text-forest-900 mb-12 text-center"><?php echo esc_html( $prg_heading ); ?></h2>

      <?php if ( ! empty( $prg_items ) && is_array( $prg_items ) ) : ?>
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php foreach ( $prg_items as $idx => $mod ) :
          $p_img_url = is_array( $mod['image'] ?? null ) ? ( $mod['image']['sizes']['medium_large'] ?? $mod['image']['url'] ) : ( is_numeric( $mod['image'] ?? null ) ? wp_get_attachment_image_url( $mod['image'], 'medium_large' ) : '' );
          if ( ! $p_img_url ) {
            $program_fallbacks = array(
              $uri . '/assets/images/imgi_46_2.jpg',
              $uri . '/assets/images/imgi_47_3.jpg',
              $uri . '/assets/images/imgi_49_Detox-Your-Body-With-Ayurveda.jpg',
              $uri . '/assets/images/imgi_48_4.jpg',
            );
            $p_img_url = $program_fallbacks[ $idx % count( $program_fallbacks ) ];
          }
        ?>
        <div class="bg-white rounded-2xl border border-forest-100 shadow-sm flex flex-col justify-between overflow-hidden group hover:shadow-md transition-all duration-300">
          <div>
            <?php if ( $p_img_url ) : ?>
            <div class="aspect-[16/10] overflow-hidden bg-forest-50 border-b border-forest-100">
              <img src="<?php echo esc_url( $p_img_url ); ?>" alt="<?php echo esc_attr( $mod['title'] ?? '' ); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </div>
            <?php endif; ?>
            <div class="p-8">
              <h3 class="font-serif text-xl font-bold text-forest-900 mb-3"><?php echo esc_html( $mod['title'] ?? '' ); ?></h3>
              <p class="text-forest-700 text-sm leading-relaxed"><?php echo esc_html( $mod['description'] ?? '' ); ?></p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Transformational Health Benefits -->
  <?php
  $ben_heading = get_field( 'pancha_benefits_heading' ) ?: 'Transformational Health Benefits';
  $ben_items   = get_field( 'pancha_benefits_items' );
  ?>
  <section class="py-16 px-6 bg-white border-b border-forest-100">
    <div class="max-w-4xl mx-auto">
      <h2 class="font-serif text-3xl sm:text-4xl font-bold text-forest-900 mb-8"><?php echo esc_html( $ben_heading ); ?></h2>

      <?php if ( ! empty( $ben_items ) && is_array( $ben_items ) ) : ?>
      <ul class="space-y-4 text-forest-800 text-base leading-relaxed">
        <?php foreach ( $ben_items as $bitem ) : ?>
        <li class="flex items-start gap-3">
          <span class="text-forest-900 font-bold">•</span>
          <div>
            <?php if ( ! empty( $bitem['title'] ) ) : ?>
            <span class="text-forest-950 font-medium"><?php echo esc_html( $bitem['title'] ); ?></span>
            <?php endif; ?>
            <?php if ( ! empty( $bitem['description'] ) ) : ?>
            <span> - <?php echo esc_html( $bitem['description'] ); ?></span>
            <?php endif; ?>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </section>

  <!-- Flexible Retreat Options -->
  <?php
  $prc_heading  = get_field( 'pancha_pricing_heading' ) ?: 'Flexible Retreat Options';
  $prc_intro    = get_field( 'pancha_pricing_intro' );
  $prc_note     = get_field( 'pancha_pricing_note' );
  $prc_packages = get_field( 'pancha_pricing_packages' );
  $prc_addons   = get_field( 'pancha_pricing_addons' );
  ?>
  <section class="py-16 px-6 bg-cream border-b border-forest-100">
    <div class="max-w-5xl mx-auto">
      <div class="mb-10">
        <h2 class="font-serif text-3xl sm:text-4xl font-bold text-forest-900 mb-4"><?php echo esc_html( $prc_heading ); ?></h2>
        <?php if ( $prc_intro ) : ?>
        <p class="text-forest-700 text-base leading-relaxed"><?php echo esc_html( $prc_intro ); ?></p>
        <?php endif; ?>
        <?php if ( $prc_note ) : ?>
        <p class="mt-3 text-sm text-forest-600 italic"><?php echo esc_html( $prc_note ); ?></p>
        <?php endif; ?>
      </div>

      <!-- Clean Pricing Table -->
      <div class="overflow-x-auto bg-white rounded-2xl border border-forest-200 shadow-sm mb-8">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-forest-900 text-white font-serif border-b border-forest-800">
              <th class="py-4 px-6 font-bold"><?php echo function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? 'Durată Program' : 'Retreat Duration'; ?></th>
              <th class="py-4 px-6 font-bold"><?php echo function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? 'Tarif' : 'Rate'; ?></th>
              <th class="py-4 px-6 font-bold"><?php echo function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? 'Total per Persoană' : 'Total per person'; ?></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-forest-100 text-forest-900">
            <?php if ( ! empty( $prc_packages ) && is_array( $prc_packages ) ) :
              foreach ( $prc_packages as $pkg ) :
            ?>
            <tr class="hover:bg-forest-50/50 transition-colors">
              <td class="py-4 px-6 font-medium">
                <?php echo esc_html( $pkg['title'] ?? '' ); ?>
              </td>
              <td class="py-4 px-6 font-medium"><?php echo esc_html( $pkg['rate'] ?? '' ); ?></td>
              <td class="py-4 px-6 font-bold text-forest-950"><?php echo esc_html( $pkg['total'] ?? '' ); ?></td>
            </tr>
            <?php endforeach; endif; ?>

            <?php if ( ! empty( $prc_addons ) && is_array( $prc_addons ) ) :
              foreach ( $prc_addons as $addon ) :
            ?>
            <tr class="bg-cream/60">
              <td class="py-4 px-6 font-medium"><?php echo esc_html( $addon['title'] ?? '' ); ?></td>
              <td class="py-4 px-6 font-medium"><?php echo esc_html( $addon['rate'] ?? '' ); ?></td>
              <td class="py-4 px-6 text-sm text-forest-600 italic"><?php echo esc_html( $addon['note'] ?? '' ); ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Start Your Ayurveda Journey Today -->
  <?php
  $cta_heading = get_field( 'pancha_cta_heading' ) ?: 'Start Your Ayurveda Journey Today';
  $cta_vision  = get_field( 'pancha_cta_vision' );
  $cta_desc    = get_field( 'pancha_cta_desc' );
  ?>
  <section class="py-16 px-6 bg-forest-900 text-white">
    <div class="max-w-3xl mx-auto text-center">
      <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-6"><?php echo esc_html( $cta_heading ); ?></h2>
      
      <?php if ( $cta_vision ) : ?>
      <p class="text-forest-100 text-lg leading-relaxed mb-4">
        <?php echo esc_html( $cta_vision ); ?>
      </p>
      <?php endif; ?>

      <?php if ( $cta_desc ) : ?>
      <p class="text-forest-200 text-base leading-relaxed mb-8">
        <?php echo esc_html( $cta_desc ); ?>
      </p>
      <?php endif; ?>

      <div>
        <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="inline-block bg-amber-500 hover:bg-amber-400 text-forest-950 font-semibold px-8 py-3.5 rounded-full transition-colors">
          Contact Us / Schedule a Consultation
        </a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
