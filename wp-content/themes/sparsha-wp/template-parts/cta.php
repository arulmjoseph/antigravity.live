<?php
if ( ! get_theme_mod( 'sparsha_cta_enable', true ) ) return;
if ( is_page_template( 'page-authentic-panchakarma-retreat.php' ) ) return;

$bg_id  = absint( get_theme_mod( 'sparsha_cta_bg', 0 ) );
$bg_url = $bg_id ? wp_get_attachment_image_url( $bg_id, 'full' ) : '';
$head   = get_theme_mod( 'sparsha_cta_heading', 'Ready to Begin Your Healing Journey?' );
$sub    = get_theme_mod( 'sparsha_cta_sub', 'Our specialists will guide you — no prior knowledge of Ayurveda needed.' );
$p_text = get_theme_mod( 'sparsha_cta_p_text', 'Book Appointment' );
$p_link = get_theme_mod( 'sparsha_cta_p_link', '/contact' );
$s_text = get_theme_mod( 'sparsha_cta_s_text', 'Gift Voucher' );
$s_link = get_theme_mod( 'sparsha_cta_s_link', '/gift-vouchers' );

$abs_link = function( $u ) {
	return ( strpos( $u, 'http' ) === 0 ) ? $u : home_url( $u );
};
?>
<section class="sparsha-cta relative overflow-hidden bg-forest-900"<?php if ( $bg_url ) : ?> style="background-image:url('<?php echo esc_url( $bg_url ); ?>');background-size:cover;background-position:center"<?php endif; ?>>
	<div class="absolute inset-0 bg-gradient-to-r from-forest-900/90 via-forest-900/75 to-forest-900/60"></div>
	<div class="relative max-w-6xl mx-auto px-6 py-20 lg:py-24 text-center">
		<?php if ( $head ) : ?>
		<h3 class="font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight"><?php echo esc_html( sparsha_t( $head ) ); ?></h3>
		<?php endif; ?>
		<?php if ( $sub ) : ?>
		<p class="text-amber-100/90 text-base md:text-lg max-w-2xl mx-auto mb-8 leading-relaxed"><?php echo esc_html( sparsha_t( $sub ) ); ?></p>
		<?php endif; ?>
		<div class="flex flex-wrap justify-center gap-4">
			<?php if ( $p_text && $p_link ) : ?>
			<a href="<?php echo esc_url( $abs_link( $p_link ) ); ?>" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-bark font-semibold px-8 py-3.5 rounded-full transition-colors shadow-lg">
				<i class="fa-solid fa-calendar-check"></i>
				<?php echo esc_html( sparsha_t( $p_text ) ); ?>
			</a>
			<?php endif; ?>
			<?php if ( $s_text && $s_link ) : ?>
			<a href="<?php echo esc_url( $abs_link( $s_link ) ); ?>" class="inline-flex items-center gap-2 border-2 border-white/60 hover:border-amber-400 text-white hover:text-amber-300 font-medium px-8 py-3.5 rounded-full transition-colors">
				<i class="fa-solid fa-gift"></i>
				<?php echo esc_html( sparsha_t( $s_text ) ); ?>
			</a>
			<?php endif; ?>
		</div>
	</div>
</section>
