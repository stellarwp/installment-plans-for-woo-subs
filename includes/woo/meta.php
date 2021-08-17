<?php
/**
 * Handle the setup inside the Woo metaboxes.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Woo\Meta;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;
use Nexcess\WooInstallmentEmails\Helpers as Helpers;
use Nexcess\WooInstallmentEmails\Utilities as Utilities;

/**
 * Start our engines.
 */
add_filter( 'product_type_options', __NAMESPACE__ . '\add_installment_option_type' );
add_action( 'woocommerce_process_product_meta_subscription', __NAMESPACE__ . '\save_installment_option_type', 10 );
add_action( 'woocommerce_process_product_meta_variable-subscription', __NAMESPACE__ . '\save_installment_option_type', 10 );

/**
 * Add a new interval to run every 4 hours.
 *
 * @param  array $schedules  The current array of intervals.
 *
 * @return array
 */
function add_installment_option_type( $product_types ) {

	// Only add it if it doesn't exist.
	if ( ! isset( $product_types['is_installments'] ) ) {

		// Add our new one.
		$product_types['is_installments'] = array(
			'id'            => '_is_installments',
			'wrapper_class' => 'show_if_subscription show_if_variable-subscription',
			'label'         => __( 'Installments', 'woocommerce-installment-emails' ),
			'description'   => __( 'This subscription will be used for installment payments.', 'woocommerce-installment-emails' ),
			'default'       => 'no',
		);
	}

	// And return the updated array.
	return $product_types;
}

/**
 * Add the installment flag to the product meta for our two subscription types.
 *
 * @param  integer $product_id  The ID of the product being saved.
 *
 * @return void
 */
function save_installment_option_type( $product_id ) {

	// Check for the POST value.
	$is_installment = isset( $_POST['_is_installments'] ) ? 'yes' : 'no';

	// Set the meta key.
	update_post_meta( $product_id, '_is_installments', $is_installment );
}
