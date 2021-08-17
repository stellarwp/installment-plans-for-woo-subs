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
 * @param  WC_Order $order  The entire order object.
 *
 * @return boolean
 */
function maybe_order_has_installments( $order ) {

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
		'base' => plugin_dir_path( WC_Subscriptions::$plugin_file ) . 'templates/',
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
	return apply_filters( Core\HOOK_PREFIX . 'alert_email_address', $template_args, $order_id, $order, $is_admin_email, $plaintext );
}
