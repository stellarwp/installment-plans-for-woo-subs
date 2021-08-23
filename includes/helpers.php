<?php
/**
 * Our helper functions to use across the plugin.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Helpers;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;
use Nexcess\WooInstallmentEmails\Utilities as Utilities;

/**
 * Check to see if WooCommerce is installed and active.
 *
 * @return boolean
 */
function maybe_woo_activated() {
	return class_exists( 'woocommerce' ) ? true : false;
}

/**
 * Check to see if WooCommerce Subscriptions is installed and active.
 *
 * @return boolean
 */
function maybe_woo_subs_activated() {
	return function_exists( 'wcs_is_subscription' ) ? true : false;
}

/**
 * Check all the products in an order for installments.
 *
 * @param  WC_Order $order          The entire order object.
 * @param  boolean  $return_length  Whether to return the length or not.
 *
 * @return boolean
 */
function maybe_order_has_installments( $order, $return_length = true ) {

	// Bail if the order isn't passed correctly.
	if ( empty( $order ) ) {
		return false;
	}

	// Now loop them and check the meta.
	foreach ( $order->get_items() as $item_id => $item_values ) {

		// Get the product ID.
		$product_id = $item_values->get_product_id();

		// Now check for the meta key.
		$maybe_key  = get_post_meta( $product_id, '_is_installments', true );

		// If we have it, return true (and we're done).
		if ( ! empty( $maybe_key ) && 'yes' === sanitize_text_field( $maybe_key ) ) {

			// Return the subscription length if requested.
			if ( false !== $return_length ) {
				$sub_length = get_post_meta( $product_id, '_subscription_length', true );

				// Return the length, which has to be at least 1.
				return ! empty( $sub_length ) ? $sub_length : 1;
			}

			// Return a boolean.
			return true;
		}

		// Nothing left inside the loop.
	}

	// Return false since it was not found.
	return false;
}

/**
 * Get the arguments for the template part based on being installments.
 *
 * @param  integer  $order_id        The ID of the order.
 * @param  WC_Order $order           The entire order object.
 * @param  boolean  $is_admin_email  Whether or not it's an admin email.
 * @param  boolean  $plaintext       If this is a plaintext email or not.
 *
 * @return array
 */
function get_order_email_template_args( $order_id = 0, $order, $is_admin_email, $plaintext = false ) {

	// Set the initial args from Subscriptions.
	$template_args  = array(
		'base' => plugin_dir_path( \WC_Subscriptions::$plugin_file ) . 'templates/',
		'file' => ( $plaintext ) ? 'emails/plain/subscription-info.php' : 'emails/subscription-info.php',
	);

	// Now get the meta for our flag.
	$installments   = get_post_meta( $order_id, '_order_has_installments', true );

	// If we have installments, use our own template setup.
	if ( ! empty( $installments ) && 'yes' === sanitize_text_field( $installments ) ) {

		// Swap the values.
		$template_args  = array(
			'base' => Core\TEMPLATES_PATH . '/',
			'file' => ( $plaintext ) ? 'emails/plain/installments-info.php' : 'emails/installments-info.php',
		);
	}

	// Return it filtered.
	return apply_filters( Core\HOOK_PREFIX . 'email_template_args', $template_args, $order_id, $order, $is_admin_email, $plaintext );
}

/**
 * Add the ordinal suffix to a number.
 *
 * @param  integer $number  The number we wanna do.
 * @param  boolean $markup  Whether to include the markup or not.
 *
 * @return string
 */
function add_ordinal_suffix( $number = 1, $markup = true ) {

	// Set a default ordinal.
	$default_ordinal    = false !== $markup ? $number . '<sup>th</sup>' : $number . 'th';

	// We have some we need to do mathletics to.
	if ( ! in_array( ( $number % 100 ), array( 11, 12, 13 ) ) ) {

		// Set an empty string.
		$ordinal_number = '';

		// Run a switch to handle 1st, 2nd, 3rd.
		switch ( $number % 10 ) {

			case 1:
				$ordinal_number = false !== $markup ? $number . '<sup>st</sup>' : $number . 'st';
				break;

			case 2:
				$ordinal_number = false !== $markup ? $number . '<sup>nd</sup>' : $number . 'nd';
				break;

			case 3:
				$ordinal_number = false !== $markup ? $number . '<sup>rd</sup>' : $number . 'rd';
				break;
		}

		// And return it.
		return apply_filters( Core\HOOK_PREFIX . 'ordinal_suffix', $ordinal_number, $number, $markup );
	}

	// This is our remaining one.
	return apply_filters( Core\HOOK_PREFIX . 'ordinal_suffix', $default_ordinal, $number, $markup );
}
