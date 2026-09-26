  <?php get_template_part( 'template-parts/cta' ); ?>

  <!-- Footer -->
  <footer class="text-forest-300" style="background: var(--footer-bg-color, #1a3a10); color: var(--footer-text-color, #dbe1d8);">
    <div class="max-w-7xl mx-auto px-6 pt-16 pb-10">

      <?php
      $has_widgets = is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' );
      ?>

      <?php if ( $has_widgets ) : ?>
      <!-- Widget-based footer columns -->
      <div class="grid lg:grid-cols-12 gap-10 mb-12">
        <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
        <div class="lg:col-span-4"><?php dynamic_sidebar( 'footer-1' ); ?></div>
        <?php endif; ?>
        <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
        <div class="lg:col-span-2"><?php dynamic_sidebar( 'footer-2' ); ?></div>
        <?php endif; ?>
        <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
        <div class="lg:col-span-2"><?php dynamic_sidebar( 'footer-3' ); ?></div>
        <?php endif; ?>
        <?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
        <div class="lg:col-span-4"><?php dynamic_sidebar( 'footer-4' ); ?></div>
        <?php endif; ?>
      </div>
      <?php else : ?>
      <!-- Default hardcoded footer (no widgets configured) -->
      <div class="grid lg:grid-cols-12 gap-10 mb-12">

        <!-- Brand -->
        <div class="lg:col-span-4">
          <div class="inline-block bg-white rounded-2xl px-4 py-2.5 mb-6 shadow-lg footer-logo">
            <?php if ( has_custom_logo() ) : ?>
              <img src="<?php echo esc_url( wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="h-10 w-auto">
            <?php else : ?>
              <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/logo-370x90-3.png" alt="<?php bloginfo( 'name' ); ?>" class="h-10 w-auto">
            <?php endif; ?>
          </div>
          <p class="text-sm leading-relaxed mb-2 max-w-xs"><?php echo esc_html( sparsha_t( get_theme_mod( 'sparsha_footer_brand_desc', 'Authentic Ayurvedic healing rooted in the traditions of Kerala, India — bringing natural wellness to the heart of Europe since 2012.' ) ) ); ?></p>
          <p class="text-forest-500 text-xs mb-6"><?php echo nl2br( esc_html( sparsha_t( get_theme_mod( 'sparsha_address', 'Bd. Pipera nr. 1-VIII D, Voluntari, Romania' ) ) ) ); ?></p>

          <div class="flex gap-3">
            <?php $fb = get_theme_mod( 'sparsha_facebook', '' ); if ( $fb ) : ?>
            <a href="<?php echo esc_url( $fb ); ?>" aria-label="Facebook" class="w-11 h-11 rounded-full bg-white hover:bg-amber-500 flex items-center justify-center !text-black hover:!text-white transition-all" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f text-lg"></i></a>
            <?php endif; ?>
            <?php $ig = get_theme_mod( 'sparsha_instagram', '' ); if ( $ig ) : ?>
            <a href="<?php echo esc_url( $ig ); ?>" aria-label="Instagram" class="w-11 h-11 rounded-full bg-white hover:bg-amber-500 flex items-center justify-center !text-black hover:!text-white transition-all" target="_blank" rel="noopener"><i class="fa-brands fa-instagram text-lg"></i></a>
            <?php endif; ?>
            <?php $yt = get_theme_mod( 'sparsha_youtube', '' ); if ( $yt ) : ?>
            <a href="<?php echo esc_url( $yt ); ?>" aria-label="YouTube" class="w-11 h-11 rounded-full bg-white hover:bg-amber-500 flex items-center justify-center !text-black hover:!text-white transition-all" target="_blank" rel="noopener"><i class="fa-brands fa-youtube text-lg"></i></a>
            <?php endif; ?>
            <?php $tt = get_theme_mod( 'sparsha_tiktok', '#' ); if ( $tt ) : ?>
            <a href="<?php echo esc_url( $tt ); ?>" aria-label="TikTok" class="w-11 h-11 rounded-full bg-white hover:bg-amber-500 flex items-center justify-center !text-black hover:!text-white transition-all" target="_blank" rel="noopener"><i class="fa-brands fa-tiktok text-lg"></i></a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Services -->
        <div class="lg:col-span-2">
          <p class="text-amber-400 text-xs font-semibold tracking-[0.18em] uppercase mb-5"><?php echo esc_html( sparsha_t( get_theme_mod( 'sparsha_footer_services_title', 'Services' ) ) ); ?></p>
          <?php
          wp_nav_menu( array(
            'theme_location' => 'footer-services',
            'container'      => false,
            'menu_class'     => 'space-y-3 text-sm sparsha-footer-menu',
            'depth'          => 1,
            'fallback_cb'    => 'sparsha_fallback_footer_nav',
          ) );
          ?>
        </div>

        <!-- Explore -->
        <div class="lg:col-span-2">
          <p class="text-amber-400 text-xs font-semibold tracking-[0.18em] uppercase mb-5"><?php echo esc_html( sparsha_t( get_theme_mod( 'sparsha_footer_explore_title', 'Explore' ) ) ); ?></p>
          <?php
          wp_nav_menu( array(
            'theme_location' => 'footer',
            'container'      => false,
            'menu_class'     => 'space-y-3 text-sm sparsha-footer-menu',
            'depth'          => 1,
            'fallback_cb'    => 'sparsha_fallback_footer_nav',
          ) );
          ?>
        </div>

        <!-- Contact -->
        <div class="lg:col-span-4">
          <p class="text-amber-400 text-xs font-semibold tracking-[0.18em] uppercase mb-5"><?php echo esc_html( sparsha_t( get_theme_mod( 'sparsha_footer_contact_title', 'Contact Us' ) ) ); ?></p>
          <div class="space-y-4">
            <div class="flex items-start gap-4">
              <div class="w-9 h-9 rounded-xl bg-forest-800 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-location-dot w-4 h-4 text-forest-400"></i></div>
              <div><p class="text-white text-sm font-medium"><?php echo esc_html( sparsha_t( 'Visit Us' ) ); ?></p><p class="text-forest-400 text-xs mt-0.5"><?php echo esc_html( sparsha_t( get_theme_mod( 'sparsha_address_short', 'Bd. Pipera nr. 1-VIII D, Voluntari' ) ) ); ?></p></div>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-9 h-9 rounded-xl bg-forest-800 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-phone w-4 h-4 text-forest-400"></i></div>
              <div><p class="text-white text-sm font-medium"><?php echo esc_html( sparsha_t( 'Call Us' ) ); ?></p><p class="text-forest-400 text-xs mt-0.5"><?php echo esc_html( get_theme_mod( 'sparsha_phone', '+36 70 562 5113' ) ); ?> · <?php echo esc_html( get_theme_mod( 'sparsha_phone_2', '+36 70 742 8192' ) ); ?></p></div>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-9 h-9 rounded-xl bg-forest-800 flex items-center justify-center flex-shrink-0"><i class="fa-solid fa-envelope w-4 h-4 text-forest-400"></i></div>
              <div><p class="text-white text-sm font-medium"><?php echo esc_html( sparsha_t( 'Email Us' ) ); ?></p><p class="text-forest-400 text-xs mt-0.5"><?php echo esc_html( get_theme_mod( 'sparsha_email', 'info@sparshacare.com' ) ); ?></p></div>
            </div>
          </div>

          <?php
          $uploaded_cards = array();
          for ( $i = 1; $i <= 5; $i++ ) {
            $img = get_theme_mod( 'sparsha_payment_card_' . $i );
            if ( $img ) {
              $uploaded_cards[] = set_url_scheme( $img, is_ssl() ? 'https' : 'https' );
            }
          }
          if ( ! empty( $uploaded_cards ) ) :
          ?>
          <!-- We Accept section -->
          <div class="pt-4 border-t border-forest-800/60 mt-5">
            <p class="text-amber-400 text-xs font-semibold uppercase tracking-wider mb-3"><?php echo esc_html( sparsha_t( 'We Accept:' ) ); ?></p>
            <div class="flex flex-wrap items-center gap-2">
              <?php foreach ( $uploaded_cards as $c_idx => $card_url ) : ?>
                <span class="inline-flex items-center justify-center h-8 px-2 bg-white rounded border border-white/20 shadow-sm">
                  <img src="<?php echo esc_url( $card_url ); ?>" alt="Payment Card <?php echo $c_idx + 1; ?>" class="h-5 w-auto object-contain">
                </span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>

      </div>
      <?php endif; ?>

      <!-- Bottom bar -->
      <div class="border-t border-forest-800 pt-6 flex flex-wrap items-center justify-between gap-4 text-xs text-forest-500">
        <p>
          <?php echo wp_kses_post( sparsha_t( get_theme_mod( 'sparsha_copyright', '© 2025 Sparsha Ayurveda Centre · Bucharest, Romania. All rights reserved.' ) ) ); ?>
          <span class="inline-block sm:inline ml-0 sm:ml-2 mt-1 sm:mt-0">· Powered by <a href="https://arulmjoseph.com/" target="_blank" rel="noopener follow" class="text-forest-400 hover:text-amber-400 transition-colors font-medium" title="Web Design and Development by Arul M Joseph">arulmjoseph.com</a></span>
        </p>
        <div class="flex items-center gap-5">
          <?php
          $is_ro = ( function_exists( 'pll_current_language' ) && pll_current_language( 'slug' ) === 'ro' ) || ( isset( $_SERVER['REQUEST_URI'] ) && preg_match( '#/ro(/|$)#i', $_SERVER['REQUEST_URI'] ) );
          $privacy_url = $is_ro ? home_url( '/ro/politica-de-confidentialitate/' ) : home_url( '/privacy-policy/' );
          $terms_url   = $is_ro ? home_url( '/ro/termeni-si-conditii/' ) : home_url( '/terms-of-use/' );
          ?>
          <a href="<?php echo esc_url( $privacy_url ); ?>" class="hover:text-amber-400 transition-colors"><?php echo esc_html( sparsha_t( 'Privacy Policy' ) ); ?></a>
          <a href="<?php echo esc_url( $terms_url ); ?>" class="hover:text-amber-400 transition-colors"><?php echo esc_html( sparsha_t( 'Terms of Use' ) ); ?></a>
        </div>
      </div>
    </div>
  </footer>

<?php
if ( get_theme_mod( 'sparsha_float_enable', true ) ) :
	$fl_phone = get_theme_mod( 'sparsha_float_phone', '' ) ?: get_theme_mod( 'sparsha_phone_href', '' );
	$fl_wa    = preg_replace( '/\D/', '', get_theme_mod( 'sparsha_float_whatsapp', '' ) ?: get_theme_mod( 'sparsha_whatsapp_href', '' ) );
	$fl_msg   = get_theme_mod( 'sparsha_float_whatsapp_msg', '' );
	$fl_pos   = get_theme_mod( 'sparsha_float_position', 'right' );
	if ( $fl_phone || $fl_wa ) :
?>
<div class="sparsha-float sparsha-float--<?php echo esc_attr( $fl_pos ); ?>" aria-label="<?php esc_attr_e( 'Contact', 'sparsha-wp' ); ?>">
	<?php if ( $fl_wa ) : ?>
	<a href="https://wa.me/<?php echo esc_attr( $fl_wa ); ?>?text=<?php echo rawurlencode( $fl_msg ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp" class="sparsha-float__btn sparsha-float__btn--wa">
		<i class="fa-brands fa-whatsapp"></i>
	</a>
	<?php endif; ?>
	<?php if ( $fl_phone ) : ?>
	<a href="tel:<?php echo esc_attr( $fl_phone ); ?>" aria-label="<?php esc_attr_e( 'Call', 'sparsha-wp' ); ?>" class="sparsha-float__btn sparsha-float__btn--phone">
		<i class="fa-solid fa-phone"></i>
	</a>
	<?php endif; ?>
</div>
<?php endif; endif; ?>

<?php wp_footer(); ?>
</body>
</html>
<?php

function sparsha_fallback_footer_nav() {
	?>
	<ul class="space-y-3 text-sm">
		<li><a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="hover:text-amber-400 transition-colors"><?php echo esc_html( sparsha_t( 'About Us' ) ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/#blog' ) ); ?>" class="hover:text-amber-400 transition-colors"><?php echo esc_html( sparsha_t( 'Journal' ) ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/pricelist' ) ); ?>" class="hover:text-amber-400 transition-colors"><?php echo esc_html( sparsha_t( 'Price List' ) ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/gift-vouchers' ) ); ?>" class="hover:text-amber-400 transition-colors"><?php echo esc_html( sparsha_t( 'Gift Vouchers' ) ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="hover:text-amber-400 transition-colors"><?php echo esc_html( sparsha_t( 'FAQ' ) ); ?></a></li>
	</ul>
	<?php
}
