<?php
/**
 * Handle some basic admin-side items.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Woo\Admin;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;
use Nexcess\WooInstallmentEmails\Helpers as Helpers;
use Nexcess\WooInstallmentEmails\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\load_admin_inline_css', 30 );
add_filter( 'manage_edit-shop_subscription_columns', __NAMESPACE__ . '\add_installment_column_to_subscriptions', 30 );
add_action( 'manage_shop_subscription_posts_custom_column', __NAMESPACE__ . '\render_installment_column_content', 20, 2 );
add_filter( 'product_type_selector', __NAMESPACE__ . '\add_installments_to_product_select', 40 );
add_filter( 'woocommerce_subscriptions_order_type_dropdown', __NAMESPACE__ . '\add_installments_to_order_select', 40 );

/**
 * Add our small bit of inline CSS to the admin.
 *
 * @return string
 */
function load_admin_inline_css() {

	// Set my CSS.
	$add_admin_css  = 'table.wp-list-table th.column-is_installment { text-align: center; }';
	$add_admin_css .= 'span.wc-installment-admin-icon { display: block; text-align: center; }';
	$add_admin_css .= 'span.wc-installment-admin-icon .dashicons { font-size: 32px; height: 32px; width: 32px; }';

	// Now run it through a filter.
	$set_custom_css = apply_filters( Core\HOOK_PREFIX . 'inline_admin_css', $add_admin_css );

	// And now load it inline.
	wp_add_inline_style( 'woocommerce_subscriptions_admin', $set_custom_css );
}

/**
 * Add a column to indicate if a subscription is also an installment plan.
 *
 * @param  array $existing_columns  The existing columns we have.
 *
 * @return array
 */
function add_installment_column_to_subscriptions( $existing_columns ) {

	// If we have the specific item in the array, add it there.
	if ( isset( $existing_columns['end_date'] ) ) {

		// Set our custom column in our handy array fixer.
		return wcie_array_insert_after( 'end_date', $existing_columns, 'is_installment', __( 'Installments', 'woocommerce-installment-emails' ) );
	}

	// Didn't have it, so just drop it on the end.
	$existing_columns['is_installment'] = __( 'Installments', 'woocommerce-installment-emails' );

	// And return it.
	return $existing_columns;
}

/**
 * Render the icon for our custom column.
 *
 * @param  string  $column_name  The name of our column.
 * @param  integer $post_id      The ID of the subscription.
 *
 * @return HTML
 */
function render_installment_column_content( $column_name, $post_id ) {

	// We only wanna do this on our column name.
	if ( 'is_installment' !== sanitize_text_field( $column_name ) ) {
		return;
	}

	// Check the meta.
	$maybe_has  = get_post_meta( $post_id, '_order_has_installments', true );

	// Bail if we don't have a "yes".
	if ( empty( $maybe_has ) || 'yes' !== sanitize_text_field( $maybe_has ) ) {
		return;
	}

	// Just echo out the checkmark.
	echo '<span class="wc-installment-admin-icon"><i class="dashicons dashicons-yes"></i></span>';
}

/**
 * Add the 'installments' product type to the WooCommerce product type select box.
 *
 * @param  array $product_types  Existing array of the product types.
 *
 * @return array                 The modified array.
 */
function add_installments_to_product_select( $product_types ) {

	// Add ours to the dropdown.
	if ( ! isset( $product_types['installments'] ) ) {
		$product_types['installments'] = __( 'Installment Plans', 'woocommerce-installment-emails' );
	}

	// And return the array.
	return $product_types;
}

/**
 * Add the 'installments' type to the WooCommerce orders type select box.
 *
 * @param  array $order_types  Existing array of the order types.
 *
 * @return array                 The modified array.
 */
function add_installments_to_order_select( $order_types ) {

	// If this already exists for some reason, bail.
	if ( isset( $order_types['installments'] ) ) {
		return $order_types;
	}

	// If we have the specific item in the array, add it there.
	if ( isset( $order_types['switch'] ) ) {

		// Set our custom column in our handy array fixer.
		return wcie_array_insert_after( 'switch', $order_types, 'installments', __( 'Installment Plans', 'woocommerce-installment-emails' ) );
	}

	// Now add this to the order types.
	$order_types['installments'] = __( 'Installment Plans', 'woocommerce-installment-emails' );

	// And return it.
	return $order_types;
}
