<?php
/**
 * WooCommerce Custom Features & Auto Coupons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Automatically apply configured coupon code if cart subtotal is less than the customizer threshold.
 * Configured via Customizer -> Auto Apply Coupon Settings.
 */
add_action( 'woocommerce_before_calculate_totals', 'sparsha_auto_apply_coupon_under_threshold', 20, 1 );

function sparsha_auto_apply_coupon_under_threshold( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	// Check if Auto Coupon is enabled in Customizer (Default: true)
	$enabled = get_theme_mod( 'auto_coupon_enabled', true );
	if ( ! $enabled ) {
		return;
	}

	if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
		return;
	}

	// Get configured coupon code and threshold from Customizer
	$raw_coupon = get_theme_mod( 'auto_coupon_code', 'US7YEPWD' );
	$coupon_code = strtolower( trim( $raw_coupon ) );
	$threshold   = (float) get_theme_mod( 'auto_coupon_threshold', 300 );

	if ( empty( $coupon_code ) || $threshold <= 0 ) {
		return;
	}

	// Calculate cart subtotal (product line totals before tax/shipping)
	$subtotal = 0;
	if ( $cart && ! $cart->is_empty() ) {
		foreach ( $cart->get_cart() as $cart_item ) {
			$subtotal += isset( $cart_item['line_subtotal'] ) ? $cart_item['line_subtotal'] : 0;
		}
	}

	if ( $subtotal > 0 && $subtotal < $threshold ) {
		// Subtotal is strictly less than threshold -> Apply coupon if not active
		if ( ! $cart->has_discount( $coupon_code ) ) {
			$cart->apply_coupon( $coupon_code );
		}
	} else {
		// Subtotal >= threshold -> Remove coupon if active
		if ( $cart->has_discount( $coupon_code ) ) {
			$cart->remove_coupon( $coupon_code );
			if ( function_exists( 'wc_clear_notices' ) ) {
				wc_clear_notices();
			}
		}
	}
}

/**
 * Suppress error messages when auto-removed coupon notice triggers
 */
add_filter( 'woocommerce_coupon_error', 'sparsha_suppress_auto_coupon_error', 10, 3 );

function sparsha_suppress_auto_coupon_error( $err, $err_code, $coupon ) {
	$configured_coupon = strtolower( trim( get_theme_mod( 'auto_coupon_code', 'US7YEPWD' ) ) );
	if ( is_object( $coupon ) && strtolower( $coupon->get_code() ) === $configured_coupon ) {
		return '';
	}
	return $err;
}

/**
 * Set default product sorting on Shop & Category archives to Latest First (Date DESC)
 */
add_filter( 'woocommerce_default_catalog_orderby', 'sparsha_default_catalog_orderby_latest' );
function sparsha_default_catalog_orderby_latest( $sort_by ) {
	return 'date';
}

add_action( 'woocommerce_product_query', 'sparsha_product_query_latest_first' );
function sparsha_product_query_latest_first( $q ) {
	if ( ! is_admin() && $q->is_main_query() ) {
		$q->set( 'orderby', 'date' );
		$q->set( 'order', 'DESC' );
	}
}
