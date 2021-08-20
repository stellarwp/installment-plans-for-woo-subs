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
 * Add the ordinal suffix to a number.
 *
 * @param  integer $num  The number we wanna do.
 *
 * @return string
 */
function wc_installment_emails_add_ordinal_suffix( $num = 1 ) {

	// We have some we need to do mathletics to.
	if ( ! in_array( ( $num % 100 ), array( 11, 12, 13 ) ) ) {

		// Set an empty string.
		$ordinal_number = '';

		// Run a switch to handle 1st, 2nd, 3rd.
		switch ( $num % 10 ) {

			case 1:
				$ordinal_number = $num . '<sup>st</sup>';
				break;

			case 2:
				$ordinal_number = $num . '<sup>nd</sup>';
				break;

			case 3:
				$ordinal_number = $num . '<sup>rd</sup>';
				break;
		}

		// And return it.
		return apply_filters( Core\HOOK_PREFIX . 'ordinal_suffix', $ordinal_number, $num );
	}

	// This is our remaining one.
	return apply_filters( Core\HOOK_PREFIX . 'ordinal_suffix', $num . '<sup>th</sup>', $num );
}

/**
 * Get all the details we put together for the email box.
 *
 * @param  object $subscription  The WC_Subscription object
 * @param  object $order         The WC_Order object
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
		'total-cost'       => sprintf( __( '%s total', 'woocommerce-installment-emails' ), $set_instalm_total ),
		'no-remaining'     => esc_html_x( 'no remaining payments', 'the payment made was the final one', 'woocommerce-installment-emails' ),
	);

	// Return the content array.
	return apply_filters( Core\HOOK_PREFIX . 'email_content_args', $set_content_array, $subscription, $order );
}
