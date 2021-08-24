<?php
/**
 * Our utility functions to use across the plugin.
 *
 * Not using a namespace on purpose so these functions can be used by others.
 *
 * @package WooInstallmentEmails
 */

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;
use Nexcess\WooInstallmentEmails\Helpers as Helpers;

/**
 * Get all the details we put together for the email box.
 *
 * @param  object  $subscription  The WC_Subscription object.
 * @param  object  $order         The WC_Order object.
 *
 * @return array
 */
function wc_installment_emails_get_content_args( $subscription, $order ) {

	// Begin by handling all our various calculations and meta pulls.

	// Set our single total and increment.
	$set_single_increm  = Helpers\add_ordinal_suffix( $subscription->get_payment_count() );
	$set_single_total   = wc_price( $subscription->get_total(), array( 'currency' => $subscription->get_currency() ) );
	$set_single_count   = get_post_meta( $order->get_id(), '_order_installment_count', true );

	// Calculate the total cost.
	$calc_instalm_total = absint( $subscription->get_total() ) * absint( $set_single_count );
	$set_instalm_total  = wc_price( $calc_instalm_total, array( 'currency' => $subscription->get_currency() ) );

	// Set an array for this.
	$set_content_array  = array(
		'payment-detail'   => sprintf( __( '%s payment of %s', 'woocommerce-installment-emails' ), $set_single_increm, $set_single_total ),
		'payment-counts'   => sprintf( _n( '%d total payment', '%d total payments', $set_single_count, 'woocommerce-installment-emails' ), $set_single_count ),
		'payment-schedule' => sprintf( __( '%s per %s', 'woocommerce-installment-emails' ), $set_single_total, $subscription->get_billing_period() ),
		'payment-totals'   => sprintf( __( '%s total', 'woocommerce-installment-emails' ), $set_instalm_total ),
		'no-remaining'     => esc_html_x( 'no remaining payments', 'the payment made was the final one', 'woocommerce-installment-emails' ),
		'single-count'     => $set_single_count,
		'total-cost'       => $set_instalm_total,
	);

	// Return the content array.
	return apply_filters( Core\HOOK_PREFIX . 'email_content_args', $set_content_array, $subscription, $order );
}

/**
 * Inserts a new key/value after the key in the array.
 *
 * @param $needle The array key to insert the element after
 * @param $haystack An array to insert the element into
 * @param $new_key The key to insert
 * @param $new_value An value to insert
 * @return The new array if the $needle key exists, otherwise an unmodified $haystack
 */
function wc_installment_emails_array_insert_after( $needle, $haystack, $new_key, $new_value ) {

	if ( array_key_exists( $needle, $haystack ) ) {

		$new_array = array();

		foreach ( $haystack as $key => $value ) {

			$new_array[ $key ] = $value;

			if ( $key === $needle ) {
				$new_array[ $new_key ] = $new_value;
			}
		}

		return $new_array;
	}

	return $haystack;
}

/**
 * Gets all the active and inactive subscriptions for a user, as specified by $user_id
 *
 * @param int $user_id (optional) The id of the user whose subscriptions you want. Defaults to the currently logged in user.
 * @since 2.0
 *
 * @return WC_Subscription[]
 */
function wc_installment_emails_get_users_installments( $user_id = 0 ) {

	// Make sure we have a user ID before we continue.
	if ( 0 === $user_id || empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	// Set an empty.
	$subscriptions = array();

	$subscription_ids = WCS_Customer_Store::instance()->get_users_subscription_ids( $user_id );

	foreach ( $subscription_ids as $subscription_id ) {
		$subscription = wcs_get_subscription( $subscription_id );

		if ( $subscription ) {

			$maybe_install = Helpers\maybe_order_has_installments( $subscription, false );

			if ( false !== $maybe_install ) {
				$subscriptions[ $subscription_id ] = $subscription;
			}
		}
	}

	preprint( $subscriptions, true );

	// And return the rest.
	return $subscriptions;
}
