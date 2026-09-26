<?php
/**
 * Blog archive template (assigned via Settings > Reading > Posts page)
 */
get_header();
$uri        = get_template_directory_uri();
$blog_head  = get_field( 'blog_index_heading', 'option' ) ?: 'Ayurvedic Journal';
$blog_sub   = get_field( 'blog_index_sub', 'option' );
$blog_bg    = get_field( 'blog_index_banner', 'option' );
$banner_url = is_array( $blog_bg ) ? $blog_bg['url'] : sparsha_page_banner_url();
?>

  <!-- Hero -->
  <?php
  if ( ! $banner_url ) {
    $banner_url = $uri . '/assets/images/imgi_49_Detox-Your-Body-With-Ayurveda.jpg';
  }
  ?>
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <img src="<?php echo esc_url( $banner_url ); ?>" alt="<?php echo esc_attr( $blog_head ); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-sm"><?php echo esc_html( $blog_head ); ?></h1>
    </div>
  </section>

  <main>
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
          <p class="text-amber-600 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( sparsha_t( $blog_head ) ); ?></p>
          <h2 class="font-serif text-4xl lg:text-5xl font-bold text-forest-900 mb-2"><?php echo esc_html( sparsha_t( get_field( 'blog_index_heading_alt', 'option' ) ?: 'Latest Articles' ) ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3 mb-5">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg shrink-0"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
          <?php if ( $blog_sub ) : ?>
          <p class="text-forest-600 max-w-md mx-auto"><?php echo esc_html( sparsha_t( $blog_sub ) ); ?></p>
          <?php endif; ?>
        </div>

        <?php if ( have_posts() ) : ?>
        <!-- Cards grid (staggered journal style) -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-10 lg:gap-8">

          <?php
          $card_styles = array(
            array( 'translate' => 'lg:-translate-y-4 lg:rotate-[-1.5deg]', 'tape_pos' => 'left-8', 'tape_rot' => 'rotate-[-3deg]', 'tape_bg' => 'rgba(217,119,6,0.55), rgba(217,119,6,0.55) 3px, rgba(217,119,6,0.35) 3px, rgba(217,119,6,0.35) 6px', 'stamp_rot' => 'rotate-[8deg]' ),
            array( 'translate' => 'lg:translate-y-6 lg:rotate-[1deg]', 'tape_pos' => 'right-6', 'tape_rot' => 'rotate-[5deg]', 'tape_bg' => 'rgba(58,90,20,0.55), rgba(58,90,20,0.55) 3px, rgba(58,90,20,0.35) 3px, rgba(58,90,20,0.35) 6px', 'stamp_rot' => 'rotate-[-6deg]' ),
            array( 'translate' => 'lg:-translate-y-2 lg:rotate-[1.5deg]', 'tape_pos' => 'left-10', 'tape_rot' => 'rotate-[-4deg]', 'tape_bg' => 'rgba(217,119,6,0.55), rgba(217,119,6,0.55) 3px, rgba(217,119,6,0.35) 3px, rgba(217,119,6,0.35) 6px', 'stamp_rot' => 'rotate-[10deg]' ),
          );
          $cat_colors = array( 'bg-forest-700 text-cream', 'bg-amber-600 text-white', 'bg-forest-900 text-amber-300' );
          $i = 0;

          while ( have_posts() ) : the_post();
            $style = $card_styles[ $i % 3 ];
            $cat_color = $cat_colors[ $i % 3 ];
            $i++;
            $categories = get_the_category();
            $cat_name   = ! empty( $categories ) ? $categories[0]->name : 'Ayurveda';
            $read_time  = ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 );
            if ( $read_time < 1 ) $read_time = 3;
          ?>
          <article class="reveal group relative <?php echo esc_attr( $style['translate'] ); ?> hover:rotate-0 hover:-translate-y-6 transition-all duration-500">
            <a href="<?php the_permalink(); ?>" class="block">
              <!-- washi tape -->
              <span class="absolute -top-3 <?php echo esc_attr( $style['tape_pos'] ); ?> w-20 h-6 bg-amber-300/70 <?php echo esc_attr( $style['tape_rot'] ); ?> z-20 shadow-sm"
                    style="background: repeating-linear-gradient(45deg, <?php echo esc_attr( $style['tape_bg'] ); ?>);"></span>
              <!-- card -->
              <div class="bg-white rounded-sm overflow-hidden shadow-[0_10px_30px_-12px_rgba(0,0,0,0.18)] group-hover:shadow-[0_20px_40px_-12px_rgba(0,0,0,0.28)] transition-shadow border-t border-l border-forest-100">
                <div class="relative h-56 overflow-hidden">
                  <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'sparsha-card', array( 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-700' ) ); ?>
                  <?php else :
                    $default_img = get_field( 'single_post_default_image', 'option' );
                    $fallback_url = is_array( $default_img ) ? ( $default_img['sizes']['medium_large'] ?? $default_img['url'] ) : sparsha_page_banner_url();
                    if ( $fallback_url ) : ?>
                    <img src="<?php echo esc_url( $fallback_url ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <?php else : ?>
                    <div class="w-full h-full bg-forest-100"></div>
                    <?php endif; ?>
                  <?php endif; ?>
                  <span class="absolute top-4 right-4 w-14 h-14 rounded-full bg-cream/95 border-2 border-amber-500 font-serif font-bold text-amber-700 text-xl flex items-center justify-center <?php echo esc_attr( $style['stamp_rot'] ); ?> shadow-md">&#8470;<?php echo esc_html( $i ); ?></span>
                </div>
                <div class="p-7">
                  <div class="flex flex-wrap items-center gap-2 mb-3 text-[10px] font-bold tracking-wider uppercase">
                    <span class="<?php echo esc_attr( $cat_color ); ?> px-2 py-1 rounded-sm"><?php echo esc_html( sparsha_t( $cat_name ) ); ?></span>
                    <span class="text-forest-500 whitespace-nowrap">&middot; <?php echo esc_html( $read_time ); ?> <?php echo esc_html( sparsha_t( 'min read' ) ); ?></span>
                  </div>
                  <h3 class="font-serif text-xl lg:text-2xl font-bold text-forest-900 mb-4 leading-snug group-hover:text-amber-700 transition-colors">
                    <?php the_title(); ?>
                  </h3>
                  <?php if ( has_excerpt() || get_the_excerpt() ) : ?>
                  <p class="text-forest-600 text-sm leading-relaxed mb-5"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                  <?php endif; ?>
                  <span class="inline-flex items-center gap-2 text-amber-600 font-semibold text-sm relative">
                    <?php echo esc_html( sparsha_label( 'label_read_more', 'Read More' ) ); ?>
                    <i class="fa-solid fa-arrow-right w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                  </span>
                </div>
              </div>
            </a>
          </article>
          <?php endwhile; ?>

        </div>

        <!-- Pagination -->
        <?php
        $pagination = paginate_links( array(
          'prev_text' => '&laquo; ' . sparsha_label( 'label_previous', 'Previous' ),
          'next_text' => sparsha_label( 'label_next', 'Next' ) . ' &raquo;',
        ) );
        if ( $pagination ) :
        ?>
        <div class="text-center mt-16 reveal">
          <div class="inline-flex items-center gap-3 text-sm font-medium text-forest-700">
            <?php echo $pagination; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php else : ?>
        <p class="text-center text-forest-500"><?php echo esc_html( sparsha_label( 'label_no_posts', 'No articles found yet. Check back soon!' ) ); ?></p>
        <?php endif; ?>

      </div>
    </section>
  </main>

<?php get_footer(); ?>
