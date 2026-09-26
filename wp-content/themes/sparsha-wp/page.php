<?php
/**
 * The template for displaying standard and legal pages
 */
get_header();
?>

  <!-- Simple Solid Color Banner (No Image) -->
  <section class="relative sparsha-page-hero text-white px-6 overflow-hidden">
    <div class="max-w-4xl mx-auto">
      <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold text-white leading-tight mb-2"><?php the_title(); ?></h1>
    </div>
  </section>

  <!-- Full White Content Area -->
  <main class="bg-white min-h-[50vh]">
    <div class="max-w-4xl mx-auto px-6 py-12 sm:py-16">
      <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'sparsha-prose text-forest-800 leading-relaxed' ); ?>>
          <?php the_content(); ?>
        </article>
      <?php endwhile; endif; ?>
    </div>
  </main>

<?php get_footer(); ?>
