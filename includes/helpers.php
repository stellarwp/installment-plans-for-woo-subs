<?php
/**
 * Our helper functions to use across the plugin.
 *
 * @package InstallmentPlansWooSubs
 */

// Declare our namespace.
namespace Nexcess\InstallmentPlansWooSubs\Helpers;

// Set our aliases.
use Nexcess\InstallmentPlansWooSubs as Core;
use Nexcess\InstallmentPlansWooSubs\Utilities as Utilities;

/**
 * Check to see if WooCommerce is installed and active.
 *
 * @return bool True if WooCommerce is active, false otherwise.
 */
function maybe_woo_activated() {
	return class_exists( 'woocommerce' ) ? true : false;
}

/**
 * Check to see if WooCommerce Subscriptions is installed and active.
 *
 * @return bool Whether or not WooCommerce Subscriptions is active.
 */
function maybe_woo_subs_activated() {
	return function_exists( 'wcs_is_subscription' ) ? true : false;
}

/**
 * Check and see if any products have been applied.
 *
 * @return bool Whether or installments is set up.
 */
function maybe_store_has_installments() {

	// Set the key to use in our transient.
	$ky = Core\TRANSIENT_PREFIX . 'has_installments';

	// If we don't want the cache'd version, delete the transient first.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG || ! empty( $purge ) ) {
		delete_transient( $ky );
	}

	// Attempt to get the result from the cache.
	$cached_results = get_transient( $ky );

	// If we have none, do the things.
	if ( false === $cached_results ) {

		// Call the global database.
		global $wpdb;

		// Set our table name.
		$table_name = $wpdb->prefix . 'postmeta';

		// Set up our query.
		$query_args = $wpdb->prepare("
			SELECT   post_id
			FROM     $table_name
			WHERE    meta_key = '%s'
			AND      meta_value = '%s'
			LIMIT    1
		", esc_attr( '_is_installments' ), esc_attr( 'yes' ) );

		// Process the query.
		$query_run  = $wpdb->get_results( $query_args );

		// Bail without any reviews.
		if ( empty( $query_run ) ) {
			return false;
		}

		// Set our transient with our data.
		set_transient( $ky, $query_run, DAY_IN_SECONDS );

		// And change the variable to do the things.
		$cached_results = $query_run;
	}

	// Return the cached value.
	return $cached_results;
}

/**
 * Check all the products in an order for installments.
 *
 * @param \WC_Order $order         The entire order object.
 * @param bool      $return_length Whether to return the length or not.
 *
 * @return bool True if any products have installments, false if not.
 */
function maybe_order_has_installments( $order, $return_length = true ) {

	// Bail if the order isn't passed correctly.
	if ( empty( $order ) ) {
		return false;
	}

	// Attempt to get our items.
	$fetch_order_items  = $order->get_items();

	// Bail if there are no items in the order.
	if ( empty( $fetch_order_items ) ) {
		return false;
	}

	// Now loop them and check the meta.
	foreach ( $fetch_order_items as $item_id => $item_values ) {

		// Get the product ID.
		$product_id = $item_values->get_product_id();

		// Skip to the next if no ID exists.
		if ( empty( $product_id ) ) {
			continue;
		}

		// Now check for the meta key.
		$maybe_key  = get_post_meta( absint( $product_id ), '_is_installments', true );

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
 * Check if we are on the installments plan page.
 *
 * @param bool $in_query Whether to check inside the actual query.
 *
 * @return bool Whether we are on the installments plan page.
 */
function maybe_installments_endpoint_page( $in_query = false ) {

	// Bail if we aren't on the right general place.
	if ( is_admin() || ! is_account_page() ) {
		return false;
	}

	// Bail if we aren't on the right general place.
	if ( $in_query && ! in_the_loop() || $in_query && ! is_main_query() ) {
		return false;
	}

	// Call the global query object.
	global $wp_query;

	// Return if we are on our specific var or not.
	return isset( $wp_query->query_vars[ Core\FRONT_VAR ] ) ? true : false;
}

/**
 * Check if we are on the subscriptions plan page.
 *
 * @param bool $in_query Whether to check inside the actual query.
 *
 * @return bool Whether we are on the subscriptions plan page.
 */
function maybe_subscriptions_endpoint_page( $in_query = false ) {

	// Bail if we aren't on the right general place.
	if ( is_admin() || ! is_account_page() ) {
		return false;
	}

	// Bail if we aren't on the right general place.
	if ( $in_query && ! in_the_loop() || $in_query && ! is_main_query() ) {
		return false;
	}

	// Call the global query object.
	global $wp_query;

	// Return if we are on our specific var or not.
	return isset( $wp_query->query_vars['subscriptions'] ) ? true : false;
}

/**
 * Get the arguments for the template part based on being installments.
 *
 * @param int       $order_id  The ID of the order.
 * @param \WC_Order $order     The entire order object.
 * @param bool      $plaintext If this is a plaintext email or not.
 *
 * @return array The arguments to pass to the template part.
 */
function get_order_email_template_args( $order_id = 0, $order, $plaintext = false ) {

	// Set the initial args from Subscriptions.
	$template_args  = array(
		'base' => plugin_dir_path( \WC_Subscriptions::$plugin_file ) . 'templates/',
		'file' => false !== $plaintext ? 'emails/plain/subscription-info.php' : 'emails/subscription-info.php',
	);

	// Now get the meta for our flag.
	$installments   = get_post_meta( absint( $order_id ), '_order_has_installments', true );

	// If we have installments, use our own template setup.
	if ( ! empty( $installments ) && 'yes' === sanitize_text_field( $installments ) ) {

		// Swap the values.
		$template_args  = array(
			'base' => Core\TEMPLATES_PATH . '/',
			'file' => false !== $plaintext ? 'emails/plain/installments-info.php' : 'emails/installments-info.php',
		);
	}

	// Return it filtered.
	return apply_filters( Core\HOOK_PREFIX . 'email_template_args', $template_args, $order_id, $order, $plaintext );
}

/**
 * Add the ordinal suffix to a number.
 *
 * @param int $number The number we wanna do.
 *
 * @return string The number with the ordinal suffix.
 */
function add_ordinal_suffix( $number = 1 ) {

	// Set a default ordinal.
	$default_ordinal    = $number . '<sup>th</sup>';

	// We have some we need to do mathletics to.
	if ( ! in_array( ( $number % 100 ), array( 11, 12, 13 ) ) ) {

		// Set an empty string.
		$ordinal_number = '';

		// Run a switch to handle 1st, 2nd, 3rd.
		switch ( $number % 10 ) {

			case 1:
				$ordinal_number = $number . '<sup>st</sup>';
				break;

			case 2:
				$ordinal_number = $number . '<sup>nd</sup>';
				break;

			case 3:
				$ordinal_number = $number . '<sup>rd</sup>';
				break;
		}

		// And return it.
		return apply_filters( Core\HOOK_PREFIX . 'ordinal_suffix', $ordinal_number, $number );
	}

	// This is our remaining one.
	return apply_filters( Core\HOOK_PREFIX . 'ordinal_suffix', $default_ordinal, $number );
}
