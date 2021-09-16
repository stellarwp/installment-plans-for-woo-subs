<?php
/**
 * Installments information template (plain text).
 *
 * This was copied from the WooCommerce Subscriptions
 * extension and then modified to fit our needs.
 *
 * @package InstallmentPlansWooSubs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( empty( $subscriptions ) ) {
	return;
}

echo "\n";
echo "\n";

esc_html_e( 'Installment Plan Information', 'installment-plans-for-woo-subs' );

echo "\n";
echo "\n";

foreach ( $subscriptions as $subscription ) {
	$content_args = wcsip_get_email_content_args( $subscription, $order );

	// translators: placeholder is installment count and amount.
	echo esc_html( sprintf( _x( 'Installment: %s', 'in plain emails for subscription information', 'installment-plans-for-woo-subs' ), wp_strip_all_tags( $content_args['payment-detail'] ) ) );

	echo "\n";

	// translators: placeholder is the localised date of payment.
	echo esc_html( sprintf( _x( 'Payment Date: %s', 'in plain emails for subscription information', 'installment-plans-for-woo-subs' ), date_i18n( wc_date_format(), $subscription->get_time( 'date_created', 'site' ) ) ) );

	echo "\n";
	echo "\n";

	// A header for the plan details.
	echo '--';
	_x( 'Plan Details', 'in plain emails for subscription information', 'installment-plans-for-woo-subs' );
	echo '--';

	echo "\n";

	// translators: placeholder is total number of installment payments.
	echo esc_html( sprintf( _x( 'Total payments: %s', 'in plain emails for subscription information', 'installment-plans-for-woo-subs' ), $content_args['single-count'] ) );

	echo "\n";

	// translators: placeholder is installment type.
	echo esc_html( sprintf( _x( 'Terms: %s', 'in plain emails for subscription information', 'installment-plans-for-woo-subs' ), wp_strip_all_tags( $content_args['payment-schedule'] ) ) );

	echo "\n";

	// translators: placeholder is the total amount of the subscription
	echo esc_html( sprintf( _x( 'Total cost: %s', 'in plain emails for subscription information', 'installment-plans-for-woo-subs' ), wp_strip_all_tags( $content_args['total-cost'] ) ) );

	echo "\n";

	if ( $subscription->get_time( 'next_payment' ) > 0 ) {
		/* translators: placeholder is the localised date of the next payment. */
		echo esc_html( sprintf( __( 'Next payment: %s', 'installment-plans-for-woo-subs' ), date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) ) );
	} else {
		/* translators: placeholder is the localised date of the next payment. */
		echo esc_html( sprintf( __( 'Next payment: %s', 'installment-plans-for-woo-subs' ), $content_args['no-remaining'] ) );
	}

	echo "\n\n";
}
if ( ! $subscription->is_manual() && ! $is_admin_email && ( $subscription->get_time( 'next_payment' ) > 0 ) ) {
	if ( 1 === count( $subscriptions ) ) {
		$subscription   = reset( $subscriptions );
		$my_account_url = $subscription->get_view_order_url();
	} else {
		$my_account_url = wc_get_endpoint_url( 'subscriptions', '', wc_get_page_permalink( 'myaccount' ) );
	}

	// Translators: Placeholder is the My Account URL.
	echo esc_html( sprintf( _n(
		'This installment plan is set to renew automatically using your payment method on file. You can manage or cancel this order from your my account page. %s',
		'These installment plans are set to renew automatically using your payment method on file. You can manage or cancel your orders from your my account page. %s',
		count( $subscriptions ),
		'installment-plans-for-woo-subs'
	), $my_account_url ) );
}
