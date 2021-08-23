<?php
/**
 * Installments information template (plain text).
 *
 * This was copied from the WooCommerce Subscriptions
 * extension and then modified to fit our needs.
 *
 * @package WooInstallmentEmails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( empty( $subscriptions ) ) {
	return;
}

$has_automatic_renewal = false;
$is_parent_order       = wcs_order_contains_subscription( $order, 'parent' );

echo "\n\n" . __( 'Installment Plan Information', 'woocommerce-installment-emails' ) . "\n\n";
foreach ( $subscriptions as $subscription ) {
	$has_automatic_renewal = $has_automatic_renewal || ! $subscription->is_manual();

	$content_args = wc_installment_emails_get_content_args( $subscription, $order );

	// translators: placeholder is installment count and amount.
	echo sprintf( _x( 'Installment: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), wp_strip_all_tags( $content_args['payment-detail'] ) ) . "\n";

	// translators: placeholder is the localised date of payment.
	echo sprintf( _x( 'Payment Date: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), date_i18n( wc_date_format(), $subscription->get_time( 'date_created', 'site' ) ) ) . "\n\n";

	// A header for the plan details.
	echo '--' . _x( 'Plan Details', 'in plain emails for subscription information', 'woocommerce-installment-emails' ) . '--' . "\n";

	// translators: placeholder is total number of installment payments.
	echo sprintf( _x( 'Total payments: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), $content_args['single-count'] ) . "\n";

	// translators: placeholder is installment type.
	echo sprintf( _x( 'Terms: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), wp_strip_all_tags( $content_args['payment-schedule'] ) ) . "\n";

	// translators: placeholder is the total amount of the subscription
	echo sprintf( _x( 'Total cost: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), wp_strip_all_tags( $content_args['total-cost'] ) ) . "\n";

	if ( $subscription->get_time( 'next_payment' ) > 0 ) {
		echo sprintf( esc_html__( 'Next payment: %s', 'woocommerce-installment-emails' ), esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) ) );
	} else {
		echo sprintf( esc_html__( 'Next payment: %s', 'woocommerce-installment-emails' ), $content_args['no-remaining'] );
	}

	echo "\n\n";
}
if ( $has_automatic_renewal && ! $is_admin_email && $subscription->get_time( 'next_payment' ) > 0 ) {
	if ( count( $subscriptions ) === 1 ) {
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
		'woocommerce-installment-emails'
	), $my_account_url ) );
}
