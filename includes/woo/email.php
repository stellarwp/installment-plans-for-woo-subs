<?php
/**
 * Handle all the changes and modifications emails, etc.
 *
 * @package InstallmentPlansWooSubs
 */

// Declare our namespace.
namespace Nexcess\InstallmentPlansWooSubs\Woo\Emails;

// Set our aliases.
use Nexcess\InstallmentPlansWooSubs as Core;
use Nexcess\InstallmentPlansWooSubs\Helpers as Helpers;
use Nexcess\InstallmentPlansWooSubs\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\remove_subscription_box' );
add_action( 'woocommerce_email_after_order_table', __NAMESPACE__ . '\maybe_add_installment_info', 15, 3 );

/**
 * Remove the default subscription info box.
 */
function remove_subscription_box() {
	remove_action( 'woocommerce_email_after_order_table', 'WC_Subscriptions_Order::add_sub_info_email', 15, 3 );
}

/**
 * Add our custom installment info box if the order is flagged.
 *
 * @param \WC_Order $order          The WooCommerce order object.
 * @param bool      $is_admin_email Whether or not it's an admin email.
 * @param bool      $plaintext      If this is a plaintext email or not.
 */
function maybe_add_installment_info( $order, $is_admin_email, $plaintext = false ) {

	// Since we're modifying the subscriptions, and still want that
	// template available for non-installments, bail without that function.
	if ( ! function_exists( 'wcs_get_subscriptions_for_order' ) ) {
		return;
	}

	// Check for the existence of subscriptions.
	$subscriptions  = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'any' ) );

	// Bail if we don't have any subs at all.
	if ( empty( $subscriptions ) ) {
		return;
	}

	// Get our template args.
	$template_args  = Helpers\get_order_email_template_args( $order->get_id(), $order, $plaintext );

	// Bail if we somehow lost the args in the filter.
	if ( empty( $template_args ) ) {
		return;
	}

	// Return the WC template setup.
	wc_get_template(
		$template_args['file'],
		array(
			'order'          => $order,
			'subscriptions'  => $subscriptions,
			'is_admin_email' => $is_admin_email,
		),
		'',
		$template_args['base']
	);
}
