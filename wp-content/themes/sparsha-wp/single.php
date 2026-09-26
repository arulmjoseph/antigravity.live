<?php get_header(); $uri = get_template_directory_uri(); ?>

<?php while ( have_posts() ) : the_post();
  $categories = get_the_category();
  $cat_name   = ! empty( $categories ) ? $categories[0]->name : 'Ayurveda';
  $cat_id     = ! empty( $categories ) ? $categories[0]->term_id : 0;
  $read_time  = ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 );
  if ( $read_time < 1 ) $read_time = 3;
?>

  <!-- Hero -->
  <section class="relative h-[38vh] sm:h-[45vh] lg:h-[50vh] min-h-[280px] sm:min-h-[360px] flex items-end overflow-hidden">
    <?php if ( has_post_thumbnail() ) : ?>
      <?php the_post_thumbnail( 'sparsha-hero', array( 'class' => 'absolute inset-0 w-full h-full object-cover object-center', 'fetchpriority' => 'high' ) ); ?>
    <?php else :
      $default_img = get_field( 'single_post_default_image', 'option' );
      $fallback_url = is_array( $default_img ) ? ( $default_img['sizes']['large'] ?? $default_img['url'] ) : sparsha_page_banner_url();
      if ( ! $fallback_url ) {
        $fallback_url = $uri . '/assets/images/imgi_49_Detox-Your-Body-With-Ayurveda.jpg';
      } ?>
      <img src="<?php echo esc_url( $fallback_url ); ?>" alt="<?php the_title_attribute(); ?>" class="absolute inset-0 w-full h-full object-cover object-center" fetchpriority="high">
    <?php endif; ?>
    <!-- Top header background gradient (fades to bottom) -->
    <div class="absolute top-0 inset-x-0 h-36 sm:h-44 bg-gradient-to-b from-forest-950/85 via-forest-950/40 to-transparent pointer-events-none z-10"></div>
    <!-- Soft bottom text backdrop -->
    <div class="absolute bottom-0 inset-x-0 h-28 sm:h-32 bg-gradient-to-t from-forest-950/75 via-forest-950/30 to-transparent pointer-events-none"></div>
    <div class="relative z-20 w-full max-w-7xl mx-auto px-6 pb-8 sm:pb-12">
      <p class="text-amber-400 text-xs font-semibold tracking-[0.2em] uppercase mb-3">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-amber-300"><?php echo esc_html( sparsha_t( 'Home' ) ); ?></a>
        <span class="mx-2 opacity-50">/</span>
        <?php
        $blog_page_id = get_option( 'page_for_posts' );
        $blog_url     = $blog_page_id ? get_permalink( $blog_page_id ) : home_url( '/blog' );
        ?>
        <a href="<?php echo esc_url( $blog_url ); ?>" class="hover:text-amber-300"><?php echo esc_html( sparsha_t( 'Journal' ) ); ?></a>
        <span class="mx-2 opacity-50">/</span>
        <?php the_title(); ?>
      </p>
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <span class="bg-amber-500/20 text-amber-300 text-xs font-semibold px-3 py-1 rounded-full border border-amber-500/30"><?php echo esc_html( $cat_name ); ?></span>
        <span class="text-white/60 text-xs">&middot; <?php echo esc_html( $read_time ); ?> <?php esc_html_e( 'min read', 'sparsha-wp' ); ?></span>
        <span class="text-white/60 text-xs">&middot; <?php echo esc_html( get_the_date() ); ?></span>
      </div>
      <h1 class="font-serif text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight max-w-4xl"><?php the_title(); ?></h1>
    </div>
  </section>

  <main>

    <!-- Article -->
    <section class="py-20 px-6 bg-cream">
      <div class="max-w-4xl mx-auto">

        <!-- Author bar -->
        <div class="flex items-center gap-4 mb-12 pb-8 border-b border-forest-100">
          <div class="w-12 h-12 rounded-full bg-forest-700 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
            <?php echo esc_html( mb_substr( get_the_author(), 0, 1 ) ); ?>
          </div>
          <div>
            <p class="text-forest-900 font-semibold text-sm"><?php the_author(); ?></p>
            <p class="text-forest-500 text-xs"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?> &middot; <?php echo esc_html( $read_time ); ?> <?php esc_html_e( 'min read', 'sparsha-wp' ); ?></p>
          </div>
        </div>

        <!-- Article content -->
        <article class="reveal sparsha-article">
          <?php the_content(); ?>
        </article>

        <!-- Tags -->
        <?php $tags = get_the_tags(); if ( $tags ) : ?>
        <div class="mt-12 pt-8 border-t border-forest-100">
          <p class="text-xs font-semibold text-forest-500 uppercase tracking-wider mb-3"><?php esc_html_e( 'Topics', 'sparsha-wp' ); ?></p>
          <div class="flex flex-wrap gap-2">
            <?php foreach ( $tags as $tag ) : ?>
            <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="bg-forest-100 text-forest-700 text-xs font-medium px-3 py-1.5 rounded-full hover:bg-forest-200 transition-colors">
              <?php echo esc_html( $tag->name ); ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Post navigation -->
        <?php
        $prev = get_previous_post();
        $next = get_next_post();
        if ( $prev || $next ) :
        ?>
        <div class="mt-12 pt-8 border-t border-forest-100 grid sm:grid-cols-2 gap-4">
          <?php if ( $prev ) : ?>
          <a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="bg-white rounded-2xl p-5 border border-forest-100 shadow-sm hover:shadow-md transition-shadow group">
            <span class="text-xs text-forest-500 uppercase tracking-wider flex items-center gap-1">
              <i class="fa-solid fa-arrow-left w-3 h-3"></i>
              <?php echo esc_html( sparsha_label( 'label_previous', 'Previous' ) ); ?>
            </span>
            <p class="font-serif font-semibold text-forest-900 mt-2 leading-snug group-hover:text-amber-700 transition-colors"><?php echo esc_html( $prev->post_title ); ?></p>
          </a>
          <?php else : ?>
          <div></div>
          <?php endif; ?>
          <?php if ( $next ) : ?>
          <a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="bg-white rounded-2xl p-5 border border-forest-100 shadow-sm hover:shadow-md transition-shadow group sm:text-right">
            <span class="text-xs text-forest-500 uppercase tracking-wider flex items-center gap-1 sm:justify-end">
              <?php echo esc_html( sparsha_label( 'label_next', 'Next' ) ); ?>
              <i class="fa-solid fa-arrow-right w-3 h-3"></i>
            </span>
            <p class="font-serif font-semibold text-forest-900 mt-2 leading-snug group-hover:text-amber-700 transition-colors"><?php echo esc_html( $next->post_title ); ?></p>
          </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>

      </div>
    </section>

    <!-- Related Posts -->
    <?php
    $related = new WP_Query( array(
      'post_type'      => 'post',
      'posts_per_page' => 3,
      'post__not_in'   => array( get_the_ID() ),
      'category__in'   => $cat_id ? array( $cat_id ) : array(),
      'orderby'        => 'rand',
    ) );
    if ( $related->have_posts() ) :
    ?>
    <section class="relative py-24 px-6 overflow-hidden bg-cream">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-14 reveal">
          <p class="text-amber-600 text-xs font-semibold tracking-[0.25em] uppercase mb-3"><?php echo esc_html( sparsha_label( 'label_related_posts', 'Related Posts' ) ); ?></p>
          <h2 class="font-serif text-3xl lg:text-4xl font-bold text-forest-900"><?php echo esc_html( get_field( 'related_posts_heading', 'option' ) ?: 'You May Also Enjoy' ); ?></h2>
          <div class="flex items-center justify-center gap-4 mt-3">
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
            <i class="fa-solid fa-fire text-amber-500 text-lg"></i>
            <div class="h-[1px] bg-forest-900/20 w-24"></div>
          </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php
          $default_img_r  = get_field( 'single_post_default_image', 'option' );
          $default_img_url = is_array( $default_img_r ) ? ( $default_img_r['sizes']['medium_large'] ?? $default_img_r['url'] ) : sparsha_page_banner_url();
          $ri = 0;
          while ( $related->have_posts() ) : $related->the_post();
            $r_cats    = get_the_category();
            $r_cat     = ! empty( $r_cats ) ? $r_cats[0]->name : 'Ayurveda';
            $r_read    = ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 );
            if ( $r_read < 1 ) $r_read = 3;
          ?>
          <article class="reveal bg-white rounded-2xl overflow-hidden shadow-sm border border-forest-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
            <div class="h-48 overflow-hidden">
              <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'sparsha-card', array( 'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500' ) ); ?>
              <?php elseif ( $default_img_url ) : ?>
                <img src="<?php echo esc_url( $default_img_url ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <?php else : ?>
                <div class="w-full h-full bg-forest-100"></div>
              <?php endif; ?>
            </div>
            <div class="p-6">
              <div class="flex items-center gap-2 mb-3 text-[10px] font-bold tracking-[0.2em] uppercase">
                <span class="bg-forest-700 text-cream px-2 py-1 rounded-sm"><?php echo esc_html( $r_cat ); ?></span>
                <span class="text-forest-500">&middot; <?php echo esc_html( $r_read ); ?> min read</span>
              </div>
              <h3 class="font-serif text-lg font-semibold text-forest-900 mb-2 leading-snug group-hover:text-amber-700 transition-colors">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              </h3>
              <p class="text-forest-600 text-sm leading-relaxed mb-4"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 15 ) ); ?></p>
              <a href="<?php the_permalink(); ?>" class="inline-flex items-center gap-1 text-amber-600 text-sm font-medium hover:text-amber-700">
                <?php echo esc_html( sparsha_label( 'label_read_more', 'Read More' ) ); ?>
                <i class="fa-solid fa-arrow-right w-3.5 h-3.5"></i>
              </a>
            </div>
          </article>
          <?php $ri++; endwhile; wp_reset_postdata(); ?>
        </div>

        <!-- Back to journal -->
        <div class="text-center mt-12 reveal">
          <a href="<?php echo esc_url( $blog_url ); ?>" class="inline-flex items-center gap-3 text-forest-700 font-semibold hover:text-amber-600 transition-colors group">
            <span class="h-px w-8 bg-forest-300 group-hover:bg-amber-500 transition-colors"></span>
            <?php esc_html_e( 'Browse all articles', 'sparsha-wp' ); ?>
            <span class="h-px w-8 bg-forest-300 group-hover:bg-amber-500 transition-colors"></span>
          </a>
        </div>
      </div>
    </section>
    <?php endif; ?>

  </main>

<?php endwhile; ?>

<?php get_footer(); ?>
