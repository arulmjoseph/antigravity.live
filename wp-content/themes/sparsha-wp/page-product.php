<?php
/*
 * Template Name: Products
 * Template Post Type: page
 */
get_header();
$banner = get_field( 'product_banner' );
?>

  <main class="bg-cream">
    <?php if ( $banner && ! empty( $banner['url'] ) ) : ?>
      <img src="<?php echo esc_url( $banner['url'] ); ?>" alt="<?php echo esc_attr( $banner['alt'] ?: get_the_title() ); ?>" class="w-full h-auto block">
    <?php else : ?>
      <div class="min-h-[60vh] flex items-center justify-center px-6 py-24 text-center">
        <div class="max-w-2xl">
          <p class="font-serif text-3xl lg:text-4xl text-forest-900 mb-4"><?php esc_html_e( 'The Alchemy is Simmering.', 'sparsha-wp' ); ?></p>
          <p class="text-forest-600 leading-relaxed mb-2"><?php esc_html_e( 'Our digital apothecary opens its doors very soon.', 'sparsha-wp' ); ?></p>
          <p class="text-forest-400 text-sm mt-8"><?php esc_html_e( 'Upload the banner image in this page’s “Product Banner” field.', 'sparsha-wp' ); ?></p>
        </div>
      </div>
    <?php endif; ?>
  </main>

<?php get_footer(); ?>
