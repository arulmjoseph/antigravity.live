<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<?php
$sticky_class      = get_theme_mod( 'sparsha_sticky_header', true ) ? 'fixed' : 'relative';
$transparent_class = get_theme_mod( 'sparsha_transparent_header', true ) ? 'sparsha-transparent-header' : '';
?>
<body <?php body_class( 'bg-cream text-bark antialiased' ); ?>>
<?php wp_body_open(); ?>

  <?php if ( get_theme_mod( 'sparsha_top_bar_enable', false ) ) : ?>
  <div class="sparsha-top-bar text-xs py-2 px-6" style="background: var(--top-bar-bg-color); color: var(--top-bar-text-color);">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      <span><?php echo esc_html( get_theme_mod( 'sparsha_top_bar_left', '' ) ); ?></span>
      <span><?php echo esc_html( get_theme_mod( 'sparsha_top_bar_right', '' ) ); ?></span>
    </div>
  </div>
  <?php endif; ?>

  <header id="nav" class="<?php echo esc_attr( $sticky_class ); ?> top-0 inset-x-0 z-50 transition-all duration-300 <?php echo esc_attr( $transparent_class ); ?>">
    <div id="nav-inner" class="transition-all duration-300">
      <div class="w-full px-6 lg:px-10 flex items-center justify-between h-16 lg:h-20">

        <!-- Logo -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3">
          <?php if ( has_custom_logo() ) : ?>
            <img src="<?php echo esc_url( wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="nav-logo h-10 lg:h-12 w-auto">
          <?php else : ?>
            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/logo-370x90-3.png" alt="<?php bloginfo( 'name' ); ?>" class="nav-logo h-10 lg:h-12 w-auto">
          <?php endif; ?>
          <?php if ( get_theme_mod( 'sparsha_show_site_title', false ) ) : ?>
            <span class="nav-brand font-serif text-2xl lg:text-3xl font-semibold tracking-wide leading-none"><?php bloginfo( 'name' ); ?></span>
          <?php endif; ?>
          <?php if ( get_theme_mod( 'sparsha_show_tagline', false ) ) : ?>
            <span class="nav-tagline text-[9px] lg:text-[10px] uppercase tracking-[0.12em] font-semibold mt-1 leading-none"><?php bloginfo( 'description' ); ?></span>
          <?php endif; ?>
        </a>

        <!-- Desktop nav -->
        <?php
        wp_nav_menu( array(
          'theme_location' => 'primary',
          'container'      => 'nav',
          'container_class'=> 'nav-links hidden lg:flex items-center text-sm font-normal',
          'items_wrap'     => '<ul id="%1$s" class="%2$s sparsha-nav-list">%3$s</ul>',
          'depth'          => 3,
          'fallback_cb'    => 'sparsha_fallback_menu',
        ) );
        ?>

        <!-- CTA & Contact -->
        <div class="flex items-center gap-3">

          <?php
          // Polylang language switcher (Direct 1-click toggle)
          if ( function_exists( 'pll_the_languages' ) ) :
            $langs = pll_the_languages( array(
              'raw'              => 1,
              'hide_if_empty'    => 0,
              'display_names_as' => 'slug',
            ) );
            if ( $langs && is_array( $langs ) && count( $langs ) > 1 ) :
          ?>
          <div class="sparsha-lang-inline flex items-center gap-1 text-xs sm:text-sm font-bold tracking-wider uppercase px-1 py-1 select-none">
            <?php
            $links = array();
            foreach ( $langs as $l ) {
              $slug = strtoupper( $l['slug'] );
              if ( ! empty( $l['current_lang'] ) ) {
                $links[] = '<span class="lang-active text-amber-400 font-extrabold cursor-default">' . esc_html( $slug ) . '</span>';
              } else {
                $links[] = '<a href="' . esc_url( $l['url'] ) . '" class="lang-link opacity-80 hover:opacity-100 hover:text-amber-300 transition-all">' . esc_html( $slug ) . '</a>';
              }
            }
            echo implode( '<span class="opacity-40 font-normal">/</span>', $links );
            ?>
          </div>
          <?php endif; endif; ?>

          <?php if ( get_theme_mod( 'sparsha_header_cta_enable', true ) ) :
            $cta_text = get_theme_mod( 'sparsha_header_cta_text', 'Book Appointment' );
            $cta_link = get_theme_mod( 'sparsha_header_cta_link', '' );
            if ( empty( $cta_link ) ) {
              $cta_link = function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? home_url( '/ro/contactati-ne/' ) : home_url( '/contact/' );
            }
            if ( $cta_text ) : ?>
            <a href="<?php echo esc_url( $cta_link ); ?>" class="header-cta-btn bg-amber-500 hover:bg-amber-400 text-bark text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-sm whitespace-nowrap"><i class="fa-solid fa-calendar-check mr-1.5 text-xs opacity-80"></i><span><?php echo esc_html( sparsha_t( $cta_text ) ); ?></span></a>
          <?php endif; endif; ?>
          <button id="menu-btn" class="lg:hidden p-2 nav-menu-btn focus:outline-none" aria-label="<?php esc_attr_e( 'Menu', 'sparsha-wp' ); ?>">
            <i class="fa-solid fa-bars text-xl" id="menu-icon-open"></i>
            <i class="fa-solid fa-xmark text-xl hidden" id="menu-icon-close" style="display: none !important;"></i>
          </button>
        </div>
      </div>

      <!-- Mobile menu -->
      <div id="mobile-menu" class="hidden lg:hidden border-t border-forest-100 bg-white">
        <?php
        wp_nav_menu( array(
          'theme_location' => 'primary',
          'container'      => false,
          'menu_class'     => 'sparsha-mobile-nav px-6 py-4 flex flex-col text-sm font-medium text-forest-700',
          'depth'          => 3,
          'fallback_cb'    => 'sparsha_fallback_mobile_menu',
        ) );
        ?>

        <!-- Mobile Language Selector & CTA -->
        <div class="px-6 py-4 border-t border-forest-100/60 flex flex-col gap-4">
          <?php
          if ( function_exists( 'pll_the_languages' ) ) :
            $m_langs = pll_the_languages( array(
              'raw'              => 1,
              'hide_if_empty'    => 0,
              'display_names_as' => 'slug',
            ) );
            if ( $m_langs && is_array( $m_langs ) && count( $m_langs ) > 1 ) :
          ?>
          <div class="flex items-center justify-between py-1">
            <span class="text-xs font-semibold text-forest-500 uppercase tracking-wider"><?php echo esc_html( sparsha_t( 'Language' ) ); ?></span>
            <div class="flex items-center gap-2 text-xs font-bold tracking-wider uppercase">
              <?php
              $lang_items = array();
              foreach ( $m_langs as $ml ) {
                $is_active = ! empty( $ml['current_lang'] );
                if ( $is_active ) {
                  $lang_items[] = '<span class="text-amber-600 font-extrabold pb-0.5 border-b-2 border-amber-500">' . esc_html( strtoupper( $ml['slug'] ) ) . '</span>';
                } else {
                  $lang_items[] = '<a href="' . esc_url( $ml['url'] ) . '" class="text-forest-600 hover:text-amber-600 transition-colors pb-0.5">' . esc_html( strtoupper( $ml['slug'] ) ) . '</a>';
                }
              }
              echo implode( '<span class="text-forest-300 font-normal">/</span>', $lang_items );
              ?>
            </div>
          </div>
          <?php endif; endif; ?>

          <?php if ( get_theme_mod( 'sparsha_header_cta_enable', true ) ) :
            $cta_text = get_theme_mod( 'sparsha_header_cta_text', 'Book Appointment' );
            $cta_link = get_theme_mod( 'sparsha_header_cta_link', '' );
            if ( empty( $cta_link ) ) {
              $cta_link = function_exists( 'pll_current_language' ) && pll_current_language() === 'ro' ? home_url( '/ro/contactati-ne/' ) : home_url( '/contact/' );
            }
            if ( $cta_text ) : ?>
            <a href="<?php echo esc_url( $cta_link ); ?>" class="w-full text-center bg-amber-500 hover:bg-amber-400 text-bark text-sm font-semibold px-5 py-3 rounded-full transition-colors shadow-sm"><?php echo esc_html( sparsha_t( $cta_text ) ); ?></a>
          <?php endif; endif; ?>
        </div>
      </div>
    </div>
  </header>

<?php

function sparsha_fallback_menu() {
	?>
	<nav class="nav-links hidden lg:flex items-center gap-8 text-sm font-medium">
		<a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="transition-colors"><?php esc_html_e( 'About', 'sparsha-wp' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/#services' ) ); ?>" class="transition-colors"><?php esc_html_e( 'Services', 'sparsha-wp' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/pricelist' ) ); ?>" class="transition-colors"><?php esc_html_e( 'Treatments', 'sparsha-wp' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/#blog' ) ); ?>" class="transition-colors"><?php esc_html_e( 'Journal', 'sparsha-wp' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="transition-colors"><?php esc_html_e( 'Contact', 'sparsha-wp' ); ?></a>
	</nav>
	<?php
}

function sparsha_fallback_mobile_menu() {
	?>
	<div class="px-6 py-4 flex flex-col gap-4 text-sm font-medium text-forest-700">
		<a href="<?php echo esc_url( home_url( '/about' ) ); ?>"><?php esc_html_e( 'About', 'sparsha-wp' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/#services' ) ); ?>"><?php esc_html_e( 'Services', 'sparsha-wp' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/pricelist' ) ); ?>"><?php esc_html_e( 'Treatments', 'sparsha-wp' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/#blog' ) ); ?>"><?php esc_html_e( 'Journal', 'sparsha-wp' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact', 'sparsha-wp' ); ?></a>
	</div>
	<?php
}
