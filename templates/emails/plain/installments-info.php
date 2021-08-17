<?php
/**
 * Installments information template (plain text).
 *
 * This is basically copied from the WooCommerce Subscriptions plugin.
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

	// translators: placeholder is subscription's number
	echo sprintf( _x( 'Subscription: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), $subscription->get_order_number() ) . "\n";
	// translators: placeholder is either view or edit url for the subscription
	echo sprintf( _x( 'View subscription: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), $is_admin_email ? wcs_get_edit_post_link( $subscription->get_id() ) : $subscription->get_view_order_url() ) . "\n";
	// translators: placeholder is localised start date
	echo sprintf( _x( 'First payment: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), date_i18n( wc_date_format(), $subscription->get_time( 'start_date', 'site' ) ) ) . "\n";

	// translators: placeholder is localised end date
	echo sprintf( _x( 'Last payment: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) ) . "\n";
	// translators: placeholder is the formatted order total for the subscription
	echo sprintf( _x( 'Payment amount: %s', 'in plain emails for subscription information', 'woocommerce-installment-emails' ), $subscription->get_formatted_order_total() );

	if ( $is_parent_order && $subscription->get_time( 'next_payment' ) > 0 ) {
		echo "\n" . sprintf( esc_html__( 'Next payment: %s', 'woocommerce-installment-emails' ), esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) ) );
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
	echo wp_kses_post( sprintf( _n(
		'This installment plan is set to renew automatically using your payment method on file. You can manage or cancel this order from your my account page. %s',
		'These installment plans are set to renew automatically using your payment method on file. You can manage or cancel your orders from your my account page. %s',
		count( $subscriptions ),
		'woocommerce-installment-emails'
	), $my_account_url ) );
}
