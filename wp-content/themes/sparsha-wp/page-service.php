<?php
/*
 * Template Name: Service
 * Template Post Type: page
 */
get_header();
$uri = get_template_directory_uri();
$hero_image   = get_field( 'hero_image' );
$hero_img_url = $hero_image ? $hero_image['url'] : $uri . '/assets/images/imgi_47_3.jpg';
$hero_img_alt = $hero_image ? $hero_image['alt'] : 'Shirodhara Panchakarma';
?>


  <!-- ===== Service: Hero Section ===== -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $hero_img_url ); ?>" alt="<?php echo esc_attr( $hero_img_alt ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <div class="flex flex-wrap items-end justify-between gap-6">
        <div>
          <span class="inline-block bg-amber-500/20 text-amber-300 text-xs font-semibold px-3 py-1 rounded-full border border-amber-500/30 mb-3">Detoxification &amp; Rejuvenation</span>
          <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm">Panchakarma<br><em class="text-amber-300 not-italic">Detox Therapy</em></h1>
        </div>
        <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="bg-amber-500 hover:bg-amber-400 text-bark font-semibold px-7 py-3.5 rounded-full transition-colors shadow-lg flex-shrink-0">
          Book This Treatment
        </a>
      </div>
    </div>
  </section>

  <main>

    <!-- ===== Service: Overview Section ===== -->
    <section class="py-20 px-6 bg-cream">
      <div class="max-w-7xl mx-auto grid lg:grid-cols-3 gap-12">

        <!-- Main content -->
        <div class="lg:col-span-2">
          <div class="reveal">
            <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-4">What is Panchakarma?</p>
            <h2 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900 mb-6">The Crown Jewel of Ayurvedic Medicine</h2>
            <div class="space-y-4 text-forest-600 leading-relaxed">
              <p>Panchakarma — literally "five actions" — is the cornerstone of Ayurvedic detoxification and rejuvenation. This comprehensive cleansing protocol systematically removes accumulated toxins (Ama) from deep within bodily tissues, restoring the body's natural intelligence and the balance of the three Doshas: Vata, Pitta and Kapha.</p>
              <p>In modern life, we accumulate toxins through pollution, processed food, stress, and irregular lifestyles. These toxins lodge in the tissues and channels of the body, causing the slow degeneration of health. Panchakarma addresses this at the root — not merely suppressing symptoms but eliminating their cause entirely.</p>
              <p>At Sparsha Ayurveda, each Panchakarma programme begins with a detailed consultation to assess your Prakriti (constitution) and Vikriti (current imbalance). The programme is then individually tailored — no two treatments are identical.</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6">
          <!-- Quick info -->
          <div class="reveal bg-forest-900 rounded-2xl p-6 text-white sticky top-24">
            <h4 class="font-serif text-lg font-semibold text-amber-300 mb-5">Programme Details</h4>
            <div class="space-y-4 text-sm">
              <div class="flex items-start gap-3 pb-4 border-b border-forest-800">
                <i class="fa-regular fa-clock w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5"></i>
                <div>
                  <p class="text-forest-400 text-xs mb-0.5">Duration</p>
                  <p class="font-medium">5, 7, 14, or 21 days</p>
                </div>
              </div>
              <div class="flex items-start gap-3 pb-4 border-b border-forest-800">
                <i class="fa-solid fa-users w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5"></i>
                <div>
                  <p class="text-forest-400 text-xs mb-0.5">Who is it for</p>
                  <p class="font-medium">All ages, tailored to your constitution</p>
                </div>
              </div>
              <div class="flex items-start gap-3 pb-4 border-b border-forest-800">
                <i class="fa-solid fa-shield-halved w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5"></i>
                <div>
                  <p class="text-forest-400 text-xs mb-0.5">Includes</p>
                  <p class="font-medium">Consultation, daily treatments &amp; herbal preparations</p>
                </div>
              </div>
              <div class="flex items-start gap-3">
                <i class="fa-solid fa-tag w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5"></i>
                <div>
                  <p class="text-forest-400 text-xs mb-0.5">Pricing</p>
                  <p class="font-medium">Contact us for personalised quote</p>
                </div>
              </div>
            </div>
            <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="mt-6 w-full block text-center bg-amber-500 hover:bg-amber-400 text-bark font-semibold py-3.5 rounded-xl transition-colors">
              Book Panchakarma
            </a>
            <a href="tel:+36705625113" class="mt-3 w-full block text-center border border-forest-700 hover:border-amber-500 text-forest-300 hover:text-amber-300 text-sm py-3 rounded-xl transition-colors">
              Call +36 70 562 5113
            </a>
          </div>

          <!-- Related services -->
          <div class="reveal bg-white rounded-2xl p-6 border border-forest-100 shadow-sm">
            <h4 class="font-semibold text-forest-900 mb-4">Other Services</h4>
            <ul class="space-y-3">
              <li><a href="<?php echo esc_url( home_url( '/pricelist' ) ); ?>" class="flex items-center gap-3 text-sm text-forest-600 hover:text-amber-600 transition-colors group"><span class="w-8 h-8 rounded-lg bg-forest-50 group-hover:bg-amber-50 flex items-center justify-center flex-shrink-0">💆</span>Massage Therapy</a></li>
              <li><a href="<?php echo esc_url( home_url( '/pricelist' ) ); ?>" class="flex items-center gap-3 text-sm text-forest-600 hover:text-amber-600 transition-colors group"><span class="w-8 h-8 rounded-lg bg-forest-50 group-hover:bg-amber-50 flex items-center justify-center flex-shrink-0">🌸</span>Women's Care</a></li>
              <li><a href="<?php echo esc_url( home_url( '/pricelist' ) ); ?>" class="flex items-center gap-3 text-sm text-forest-600 hover:text-amber-600 transition-colors group"><span class="w-8 h-8 rounded-lg bg-forest-50 group-hover:bg-amber-50 flex items-center justify-center flex-shrink-0">✨</span>Facial &amp; Skin Care</a></li>
              <li><a href="<?php echo esc_url( home_url( '/pricelist' ) ); ?>" class="flex items-center gap-3 text-sm text-forest-600 hover:text-amber-600 transition-colors group"><span class="w-8 h-8 rounded-lg bg-forest-50 group-hover:bg-amber-50 flex items-center justify-center flex-shrink-0">🧘</span>Pure Relaxation</a></li>
            </ul>
          </div>
        </aside>
    
fects.</p>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ===== Service: Gallery Section ===== -->
    <section class="py-16 px-6 bg-forest-50">
      <div class="max-w-7xl mx-auto">
        <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3 reveal">Treatment Gallery</p>
        <h3 class="font-serif text-3xl font-bold text-forest-900 mb-8 reveal">See the Treatments in Action</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="reveal rounded-2xl overflow-hidden aspect-square">
            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/imgi_49_Detox-Your-Body-With-Ayurveda.jpg" alt="Herbal preparation" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
          </div>
          <div class="reveal rounded-2xl overflow-hidden aspect-square">
            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/imgi_46_2.jpg" alt="Kati Basti treatment" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
          </div>
          <div class="reveal rounded-2xl overflow-hidden aspect-square">
            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/imgi_47_3.jpg" alt="Shirodhara" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
          </div>
          <div class="reveal rounded-2xl overflow-hidden aspect-square">
            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/imgi_48_4.jpg" alt="Oil massage" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
          </div>
        </div>
      </div>
    </section>

    <!-- ===== Service: Steps Section ===== -->
    <section class="py-20 px-6 bg-white">
      <div class="max-w-4xl mx-auto">
        <div class="text-center mb-14 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.2em] uppercase mb-3">Your Journey</p>
          <h3 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900">What to Expect</h3>
        </div>
        <div class="space-y-6">
          <div class="reveal flex gap-6 items-start">
            <div class="w-12 h-12 rounded-full bg-forest-900 text-amber-400 font-bold text-lg flex items-center justify-center flex-shrink-0">1</div>
            <div class="pt-1">
              <h4 class="font-serif text-xl font-semibold text-forest-900 mb-2">Initial Consultation</h4>
              <p class="text-forest-600 text-sm leading-relaxed">Dr. Girish Mokeri assesses your Prakriti (body constitution) and current health status through pulse diagnosis (Nadi Pariksha), physical examination, and detailed health history. This determines which Panchakarma procedures are most appropriate for you.</p>
            </div>
          </div>
          <div class="reveal flex gap-6 items-start">
            <div class="w-12 h-12 rounded-full bg-forest-900 text-amber-400 font-bold text-lg flex items-center justify-center flex-shrink-0">2</div>
            <div class="pt-1">
              <h4 class="font-serif text-xl font-semibold text-forest-900 mb-2">Purvakarma – Preparation Phase</h4>
              <p class="text-forest-600 text-sm leading-relaxed">Before the main procedures begin, the body is prepared through Snehana (internal and external oleation with medicated ghee and oils) and Swedana (herbal steam therapy). This loosens and mobilises toxins from the tissues into the digestive tract for elimination.</p>
            </div>
          </div>
          <div class="reveal flex gap-6 items-start">
            <div class="w-12 h-12 rounded-full bg-forest-900 text-amber-400 font-bold text-lg flex items-center justify-center flex-shrink-0">3</div>
            <div class="pt-1">
              <h4 class="font-serif text-xl font-semibold text-forest-900 mb-2">Pradhanakarma – Main Procedures</h4>
              <p class="text-forest-600 text-sm leading-relaxed">The selected Panchakarma procedures are administered over the programme duration — typically 5 to 21 days. Each day includes one or more classical procedures combined with supportive treatments such as Abhyanga (oil massage), Shirodhara, or Kizhi.</p>
            </div>
          </div>
          <div class="reveal flex gap-6 items-start">
            <div class="w-12 h-12 rounded-full bg-forest-900 text-amber-400 font-bold text-lg flex items-center justify-center flex-shrink-0">4</div>
            <div class="pt-1">
              <h4 class="font-serif text-xl font-semibold text-forest-900 mb-2">Paschatkarma – Post-Treatment Care</h4>
              <p class="text-forest-600 text-sm leading-relaxed">After the programme, our specialists guide you through a personalised diet and lifestyle plan (Samsarjana Krama) to help the body reintegrate and maintain the benefits of the treatment. Herbal supplements may be prescribed.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA BANNER -->
    <section class="relative py-20 px-6 overflow-hidden" style="background: linear-gradient(135deg, #f5ebd9 0%, #ead9bc 50%, #d9c89f 100%);">

      <!-- soft watercolor blobs -->
      <div class="absolute -top-10 -left-16 w-72 h-72 rounded-full opacity-30 blur-3xl" style="background: radial-gradient(circle, #8cc872 0%, transparent 70%);"></div>
      <div class="absolute -bottom-12 -right-20 w-80 h-80 rounded-full opacity-30 blur-3xl" style="background: radial-gradient(circle, #d97706 0%, transparent 70%);"></div>

      <!-- left botanical accent -->
      <svg class="hidden md:block absolute left-4 lg:left-12 top-1/2 -translate-y-1/2 w-32 lg:w-44 opacity-[0.35] pointer-events-none" viewBox="0 0 200 320" fill="none" stroke="#285a14" stroke-width="1.4" stroke-linecap="round">
        <path d="M100 320 C 110 240 90 160 100 80 C 105 50 100 30 100 10"/>
        <path d="M100 280 C 60 270 30 245 22 210 C 55 220 85 245 100 280Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 220 C 65 215 40 195 35 165 C 65 175 92 195 100 220Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 160 C 72 158 52 140 48 115 C 72 124 95 142 100 160Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 105 C 80 105 65 90 62 70 C 80 76 96 92 100 105Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 250 C 140 245 170 222 180 188 C 145 196 115 220 100 250Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 190 C 135 188 160 168 168 140 C 140 148 110 168 100 190Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 130 C 128 128 148 112 154 90 C 130 96 108 112 100 130Z" fill="#479228" fill-opacity="0.35"/>
        <path d="M100 75 C 120 73 134 60 138 42 C 120 48 104 60 100 75Z" fill="#479228" fill-opacity="0.35"/>
      </svg>

      <!-- right botanical accent (mirrored) -->
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
      <div class="relative max-w-3xl mx-auto text-center reveal">
        <!-- small leaf icon above text -->
        <div class="flex items-center justify-center mb-5">
          <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
        </div>
        <h2 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-forest-900 leading-snug mb-2">
          Begin Your <em class="text-amber-700 not-italic">Panchakarma</em> Journey
        </h2>
        <p class="font-serif text-base sm:text-lg italic text-forest-700 mb-9">
          Contact us to schedule your initial consultation and find out which programme is right for you.
        </p>
        <div class="flex flex-wrap gap-4 justify-center">
          <a href="<?php echo esc_url( function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? home_url( '/ro/contactati-ne/' ) : home_url( '/contact/' ) ); ?>" class="inline-flex items-center gap-2 bg-forest-800 hover:bg-forest-900 text-amber-100 font-semibold text-xs tracking-[0.25em] uppercase px-9 py-4 rounded-full transition-all duration-300 shadow-lg shadow-forest-900/20 hover:shadow-xl hover:shadow-forest-900/30 hover:-translate-y-0.5">
            <?php echo esc_html( sparsha_t( 'Book Consultation' ) ); ?>
            <i class="fa-solid fa-arrow-right w-4 h-4"></i>
          </a>
          <a href="<?php echo esc_url( function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? home_url( '/ro/treatments/' ) : home_url( '/treatments/' ) ); ?>" class="inline-flex items-center gap-2 border-2 border-forest-800/60 hover:bg-forest-800 hover:text-amber-100 hover:border-forest-800 text-forest-800 font-semibold text-xs tracking-[0.25em] uppercase px-9 py-4 rounded-full transition-all duration-300">
            <?php echo esc_html( sparsha_t( 'All Services' ) ); ?>
          </a>
        </div>
      </div>
    </section>

  </main>


<?php get_footer(); ?>
