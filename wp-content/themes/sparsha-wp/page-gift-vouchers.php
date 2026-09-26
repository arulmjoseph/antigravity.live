<?php
/*
 * Template Name: Gift Vouchers
 * Template Post Type: page
 */
get_header();
$uri = get_template_directory_uri();
$hero_image   = get_field( 'hero_image' );
$hero_img_url = $hero_image ? $hero_image['url'] : $uri . '/assets/images/imgi_46_2.jpg';
$hero_img_alt = $hero_image ? $hero_image['alt'] : 'Ayurvedic Treatment Oils';
?>


  <!-- ===== Vouchers: Hero Section ===== -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $hero_img_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <?php $hero_cta = get_field( 'hero_cta' ); ?>
      <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
          <span class="inline-block bg-amber-500/20 text-amber-300 text-xs font-semibold px-3 py-1 rounded-full border border-amber-500/30 mb-3"><?php echo esc_html( get_field( 'hero_tag' ) ?: 'Perfect Gift for Loved Ones' ); ?></span>
          <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( get_field( 'hero_heading' ) ?: 'Gift Vouchers' ); ?><?php $hh = get_field( 'hero_highlight' ); if ( $hh !== '' ) : ?><br><em class="text-amber-300 not-italic"><?php echo esc_html( $hh ?: 'of Relaxation' ); ?></em><?php endif; ?></h1>
        </div>
        <a href="<?php echo esc_url( $hero_cta['url'] ?? '#vouchers-list' ); ?>" class="bg-amber-500 hover:bg-amber-400 text-bark font-semibold px-7 py-3.5 rounded-full transition-colors shadow-lg flex-shrink-0">
          <?php echo esc_html( $hero_cta['title'] ?? 'View Gift Options' ); ?>
        </a>
      </div>
    </div>
  </section>

  <main>
    
    <!-- Intro Section -->
    <section class="pt-16 pb-4 px-6 bg-cream">
      <div class="max-w-4xl mx-auto text-center reveal">
        <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-4"><?php echo esc_html( get_field( 'intro_label' ) ?: 'Give the Gift of Wellness' ); ?></p>
        <h2 class="font-serif text-3xl sm:text-4xl font-bold text-forest-900 mb-6"><?php echo esc_html( get_field( 'intro_heading' ) ?: 'Looking for the perfect gift for your loved ones?' ); ?></h2>
        <div class="text-forest-700 leading-relaxed text-lg sm:text-xl space-y-6 [&_em]:italic [&_strong]:font-semibold">
          <?php echo wp_kses_post( get_field( 'intro_content' ) ?: '<p>Treat them to a rejuvenating experience at Sparsha Ayurveda. Our gift cards are the ideal present for any occasion, allowing your friends and family to unwind and de-stress in our tranquil oasis.</p><p class="font-serif italic text-lg text-amber-700 font-medium">Purchase Your Gift Voucher Online – A Gateway to Ancient Wellness!</p>' ); ?>
        </div>
      </div>
    </section>

    <!-- ===== Vouchers: Process Section (vertical timeline) ===== -->
    <?php
    $proc_steps = get_field( 'process_steps' ) ?: array(
      array( 'title' => 'Select Your Desired Service', 'description' => 'Browse through our menu of Ayurvedic treatments and wellness services and choose the one you’d like to gift.' ),
      array( 'title' => 'Send Us Your Request', 'description' => 'Share your selected service with us, and we’ll guide you through the process.' ),
      array( 'title' => 'Receive Payment Details', 'description' => 'Once we receive your request, we’ll provide you with our bank details for a seamless payment transfer.' ),
      array( 'title' => 'Get Your Digital Gift Voucher', 'description' => 'After your payment is confirmed, we’ll send you a digital copy of the gift voucher, complete with a valid date and instructions.' ),
      array( 'title' => 'Redeem Your Gift', 'description' => 'The recipient can contact us via email or phone (details provided on the voucher) to book their Ayurvedic experience.' ),
    );
    // FA icons matched to each step (by index)
    $proc_icons = array(
      'fa-solid fa-clipboard-list',
      'fa-solid fa-paper-plane',
      'fa-solid fa-credit-card',
      'fa-solid fa-gift',
      'fa-solid fa-calendar-check',
    );
    ?>
    <section class="relative pt-4 pb-20 px-6 bg-cream overflow-hidden">
      <!-- decorative leaves -->
      <i class="fa-solid fa-leaf hidden md:block absolute top-20 left-4 text-forest-700 text-6xl opacity-[0.15] pointer-events-none -rotate-12"></i>
      <i class="fa-solid fa-leaf hidden md:block absolute bottom-20 right-4 text-forest-700 text-6xl opacity-[0.15] pointer-events-none rotate-12 scale-x-[-1]"></i>

      <div class="relative max-w-3xl mx-auto">
        <div class="text-center mb-16 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( get_field( 'process_label' ) ?: 'Online Process' ); ?></p>
          <h2 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900"><?php echo esc_html( get_field( 'process_heading' ) ?: 'How to Purchase & Redeem' ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-4">
            <div class="h-[1px] bg-forest-900/20 w-20"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg"></i>
            <div class="h-[1px] bg-forest-900/20 w-20"></div>
          </div>
        </div>

        <div class="relative">
          <!-- vertical dashed connector -->
          <div class="absolute left-7 top-8 bottom-8 w-px border-l-2 border-dashed border-forest-200" aria-hidden="true"></div>

          <?php foreach ( $proc_steps as $i => $step ) :
            $icon = isset( $proc_icons[ $i ] ) ? $proc_icons[ $i ] : $proc_icons[0];
          ?>
          <div class="reveal relative flex gap-6 <?php echo ( $i < count( $proc_steps ) - 1 ) ? 'pb-12' : ''; ?>">
            <!-- icon node -->
            <div class="relative z-10 w-14 h-14 rounded-full bg-forest-50 border border-forest-100 shadow-sm flex items-center justify-center shrink-0">
              <i class="<?php echo esc_attr( $icon ); ?> text-forest-700 text-xl"></i>
            </div>
            <!-- content -->
            <div class="pt-1.5">
              <h3 class="font-serif text-2xl lg:text-[1.7rem] font-bold text-forest-900 mb-2 leading-tight">
                <span class="text-amber-600"><?php echo esc_html( $i + 1 ); ?>.</span> <?php echo esc_html( $step['title'] ); ?>
              </h3>
              <p class="text-forest-600 leading-relaxed"><?php echo esc_html( $step['description'] ); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <?php if ( get_field( 'physical_enable' ) !== false ) :
      $physical_cta = get_field( 'physical_cta' );
    ?>
    <!-- Physical Copy Section -->
    <section class="relative py-20 px-6 overflow-hidden" style="background: linear-gradient(135deg, #f5ebd9 0%, #ead9bc 50%, #d9c89f 100%);">
      <div class="absolute -top-10 -left-16 w-72 h-72 rounded-full opacity-25 blur-3xl" style="background: radial-gradient(circle, #8cc872 0%, transparent 70%);"></div>
      <div class="absolute -bottom-12 -right-20 w-80 h-80 rounded-full opacity-25 blur-3xl" style="background: radial-gradient(circle, #d97706 0%, transparent 70%);"></div>

      <div class="relative max-w-3xl mx-auto text-center reveal">
        <div class="w-12 h-12 bg-forest-900 text-amber-300 rounded-full flex items-center justify-center mx-auto mb-5">
          <i class="fa-solid fa-envelope"></i>
        </div>
        <h3 class="font-serif text-3xl sm:text-4xl font-bold text-forest-900 mb-4"><?php echo esc_html( get_field( 'physical_heading' ) ?: 'Prefer a Physical Copy?' ); ?></h3>
        <div class="text-forest-700 text-lg sm:text-xl leading-relaxed mb-8 max-w-xl mx-auto [&_em]:italic [&_strong]:font-semibold space-y-4">
          <?php echo wp_kses_post( get_field( 'physical_content' ) ?: '<p>Visit our Ayurveda Centre in person to receive a beautifully crafted physical gift voucher card wrapped in premium packaging. Perfect for immediate gifting.</p>' ); ?>
        </div>
        <a href="<?php echo esc_url( $physical_cta['url'] ?? home_url( '/contact' ) ); ?>" class="mt-4 inline-flex items-center gap-2 bg-forest-800 hover:bg-forest-900 text-amber-100 font-semibold text-xs tracking-wider uppercase px-8 py-3.5 rounded-full transition-all duration-300 shadow-lg shadow-forest-900/10 hover:-translate-y-0.5">
          <?php echo esc_html( $physical_cta['title'] ?? 'Reach Out To Us Now' ); ?>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </section>
    <?php endif; ?>

    <!-- ===== Vouchers: Catalog Section ===== -->
    <section id="vouchers-list" class="py-24 px-6 bg-cream scroll-mt-20">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3"><?php echo esc_html( get_field( 'catalog_label' ) ?: 'Gift Catalog' ); ?></p>
          <h2 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900"><?php echo esc_html( get_field( 'catalog_heading' ) ?: 'Select Your Gift Voucher' ); ?></h2>
          <div class="h-[1px] bg-forest-900/20 w-24 mx-auto mt-4 mb-5"></div>
          <p class="text-forest-600 max-w-xl mx-auto leading-relaxed"><?php echo esc_html( get_field( 'catalog_description' ) ?: 'Choose a specific treatment package or select a monetary value card, allowing your recipient to customize their healing journey.' ); ?></p>
        </div>

        <?php $voucher_items = get_field( 'voucher_items' ); if ( $voucher_items ) : ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ( $voucher_items as $v ) :
            $featured = ! empty( $v['featured'] );
            $tag_classes  = $featured ? 'bg-amber-100 text-amber-800' : 'bg-forest-50 text-forest-700';
            $price_class  = $featured ? 'text-amber-700' : 'text-forest-900';
            $btn_classes  = $featured ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-forest-800 hover:bg-forest-900 text-white';
            $card_extra   = $featured ? 'border-amber-500/20 bg-amber-50/10 shadow-lg' : '';
            $border_top   = $featured ? 'border-amber-500/10' : 'border-forest-100/50';
            $link         = $v['link'];
          ?>
          <div class="reveal voucher-card rounded-3xl p-8 flex flex-col justify-between border <?php echo esc_attr( $card_extra ); ?>">
            <div>
              <div class="flex justify-between items-start gap-4 mb-4">
                <?php if ( ! empty( $v['tag'] ) ) : ?>
                <span class="<?php echo esc_attr( $tag_classes ); ?> text-[10px] font-bold tracking-wider uppercase px-3 py-1 rounded-full"><?php echo esc_html( $v['tag'] ); ?></span>
                <?php endif; ?>
                <?php if ( ! empty( $v['duration'] ) ) : ?>
                <span class="font-serif text-xs font-medium <?php echo $featured ? 'text-amber-600 font-bold' : 'text-forest-500'; ?>"><?php echo esc_html( $v['duration'] ); ?></span>
                <?php endif; ?>
              </div>
              <h3 class="font-serif text-xl font-bold text-forest-900 mb-3 leading-snug"><?php echo esc_html( $v['title'] ); ?></h3>
              <?php if ( ! empty( $v['description'] ) ) : ?>
              <p class="text-forest-600 text-sm leading-relaxed mb-6"><?php echo esc_html( $v['description'] ); ?></p>
              <?php endif; ?>
            </div>
            <div class="flex items-center justify-between border-t <?php echo esc_attr( $border_top ); ?> pt-5 mt-auto">
              <span class="text-2xl font-bold <?php echo esc_attr( $price_class ); ?> font-serif"><?php echo esc_html( $v['price'] ); ?></span>
              <a href="<?php echo esc_url( $link['url'] ?? home_url( '/contact' ) ); ?>" class="<?php echo esc_attr( $btn_classes ); ?> text-xs font-bold px-4 py-2.5 rounded-full transition-colors"><?php echo esc_html( $link['title'] ?? 'Select & Order' ); ?></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

  </main>


<?php get_footer(); ?>
