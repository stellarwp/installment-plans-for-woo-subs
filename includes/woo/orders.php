<?php
/**
 * Handle anything we need to do to the orders.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Woo\Orders;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;
use Nexcess\WooInstallmentEmails\Helpers as Helpers;
use Nexcess\WooInstallmentEmails\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'woocommerce_checkout_create_order', __NAMESPACE__ . '\save_installment_order_meta', 20, 2 );

/**
 * Add to the order meta if someone purchased an installment.
 *
 * @param  object $order  The WooCommerce order object.
 * @param  array  $data   The data being passed to make the order.
 *
 * @return void
 */
function save_installment_order_meta( $order, $data ) {

	// Check for the installments.
	$maybe_has_installments = Helpers\maybe_order_has_installments( $order );

	// If we have some, set the meta.
	if ( false !== $maybe_has_installments ) {
		$order->update_meta_data( '_order_has_installments', 'yes' );
	}
}
