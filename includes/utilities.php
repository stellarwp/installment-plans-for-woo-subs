<?php
/**
 * Our utility functions to use across the plugin.
 *
 * Not using a namespace on purpose so these functions can be used by others.
 *
 * @package InstallmentPlansWooSubs
 */

// Set our aliases.
use Nexcess\InstallmentPlansWooSubs as Core;
use Nexcess\InstallmentPlansWooSubs\Helpers as Helpers;

/**
 * Get all the details we put together for the email box.
 *
 * @param  object  $subscription  The WC_Subscription object.
 * @param  object  $order         The WC_Order object.
 *
 * @return array
 */
function wcsip_get_email_content_args( $subscription, $order ) {

	// Begin by handling all our various calculations and meta pulls.
	$get_single_count   = get_post_meta( $order->get_id(), '_order_installment_count', true );

	// Set our single total and increment.
	$set_single_count   = ! empty( $get_single_count ) ? $get_single_count : 0;
	$set_single_increm = Helpers\add_ordinal_suffix( $subscription->get_payment_count() );
	$set_single_total  = wc_price( $subscription->get_total(), [ 'currency' => $subscription->get_currency() ] );

	// Calculate the total cost.
	$calc_instalm_total = absint( $subscription->get_total() ) * absint( $set_single_count );
	$set_instalm_total  = wc_price( $calc_instalm_total, [ 'currency' => $subscription->get_currency() ] );

	// Set an array for this.
		'payment-detail'   => sprintf( __( '%s payment of %s', 'installment-plans-for-woo-subs' ), $set_single_increm, $set_single_total ),
	$set_content_array = [
		'payment-counts'   => sprintf( _n( '%d total payment', '%d total payments', $set_single_count, 'installment-plans-for-woo-subs' ), $set_single_count ),
		'payment-schedule' => sprintf( __( '%s per %s', 'installment-plans-for-woo-subs' ), $set_single_total, $subscription->get_billing_period() ),
		'payment-totals'   => sprintf( __( '%s total', 'installment-plans-for-woo-subs' ), $set_instalm_total ),
		'no-remaining'     => esc_html_x( 'no remaining payments', 'the payment made was the final one', 'installment-plans-for-woo-subs' ),
		'single-count'     => $set_single_count,
		'total-cost'       => $set_instalm_total,
	];

	// Return the content array.
	return apply_filters( Core\HOOK_PREFIX . 'email_content_args', $set_content_array, $subscription, $order );
}

/**
 * Inserts a new key/value after the key in the array.
 *
 * @param  string $needle     The array key to insert the element after.
 * @param  array  $haystack   An array to insert the element into.
 * @param  string $new_key    The key to insert.
 * @param  mixed  $new_value  An value to insert.
 *
 * @return array              The new array if the $needle key exists, otherwise an unmodified $haystack
 */
function wcsip_array_insert_after( $needle = '', $haystack = [], $new_key = '', $new_value = null ) {

	// If any required parts are missing, or the haystack isn't an array, or the The array key does't exist, return the haystack.
	if ( ! $needle || ! is_array( $haystack ) || empty( $haystack ) || ! array_key_exists( $needle, $haystack ) || ! $new_key ) {
		return $haystack;
	}

	// Set our merged array.
	$merged_array = [];

	// Loop the haystack and find our key.
	foreach ( $haystack as $haystack_key => $haystack_value ) {

		// Set the new array.
		$merged_array[ $haystack_key ] = $haystack_value;

		// If this key is our entry point, add it.
		if ( $haystack_key === $needle ) {
			$merged_array[ $new_key ] = $new_value;
		}
	}

	// Return the resulting array.
	return $merged_array;
}

/**
 * Get all the active (and inactive) installment based subscriptions for a user.
 *
 * @param  integer $user_id       The ID of the user whose subscriptions you want. Defaults to the currently logged in user.
 * @param  boolean $return_count  Whether to return the subscriptions or just the count.
 *
 * @return mixed                  Either an array of subscription objects, a count, or false.
 */
function wcsip_get_user_installments( $user_id = 0, $return_count = false ) {

	// Make sure we have a user ID before we continue.
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	// Attempt to fetch the IDs.
	$fetch_sub_ids = WCS_Customer_Store::instance()->get_users_subscription_ids( $user_id );

	// Bail without any IDs.
	if ( empty( $fetch_sub_ids ) ) {
		return false;
	}

	// Set an empty array.
	$subscriptions = [];

	// Now loop the IDs and pull out each one.
	foreach ( $fetch_sub_ids as $single_id ) {

		// Attempt to get the subscription.
		$subscription = wcs_get_subscription( $single_id );

		// If we have installments, add it.
		if ( $subscription && Helpers\maybe_order_has_installments( $subscription, false ) ) {
			$subscriptions[ $single_id ] = $subscription;
		}
	}

	// Return the array or the count based on the request.
	return $return_count ? count( $subscriptions ) : $subscriptions;
}
