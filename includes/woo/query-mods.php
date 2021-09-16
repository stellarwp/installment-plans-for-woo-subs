<?php
/**
 * Our functions related to modifying queries "my account" page.
 *
 * Set up the actions that happen inside the admin area.
 *
 * @package InstallmentPlansWooSubs
 */

// Declare our namespace.
namespace Nexcess\InstallmentPlansWooSubs\Woo\QueryMods;

// Set our aliases.
use Nexcess\InstallmentPlansWooSubs as Core;

/**
 * Start our engines.
 */
add_action( 'init', __NAMESPACE__ . '\maybe_finish_installments_setup' );
add_action( 'init', __NAMESPACE__ . '\add_installments_rewrite_endpoint' );
add_filter( 'query_vars', __NAMESPACE__ . '\add_installments_endpoint_vars', 0 );
add_filter( 'woocommerce_get_query_vars', __NAMESPACE__ . '\add_woo_query_vars' );
add_filter( 'wcs_get_users_subscriptions', __NAMESPACE__ . '\remove_installments_from_list', 20, 2 );
add_filter( 'request', __NAMESPACE__ . '\modify_installment_product_queries', 21 );
add_filter( 'request', __NAMESPACE__ . '\modify_installment_order_queries', 21 );

/**
 * Check to see if we've finished our setup.
 *
 * @return void
 */
function maybe_finish_installments_setup() {

	// Grab our option flag.
	$has_completed = get_option( Core\OPTION_PREFIX . 'activation_complete', false );

	// It's there and flagged as "yes", so we're done.
	if ( 'yes' === sanitize_text_field( $has_completed ) ) {
		return;
	}

	// It's not there, or isn't a yes, so flush the rules.
	flush_rewrite_rules();

	// Update the option.
	update_option( Core\OPTION_PREFIX . 'activation_complete', 'yes', false );
}

/**
 * Register new endpoint to use inside My Account page.
 *
 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
 */
function add_installments_rewrite_endpoint() {
	add_rewrite_endpoint( Core\FRONT_VAR, EP_ROOT | EP_PAGES );
}

/**
 * Add new query var for the installments endpoint.
 *
 * @param  array $vars  The existing query vars.
 *
 * @return array
 */
function add_installments_endpoint_vars( $vars ) {

	// Add our new endpoint var.
	$vars[] = Core\FRONT_VAR;

	// And return it.
	return $vars;
}

/**
 * Hooks into `woocommerce_get_query_vars` to make sure query vars defined in
 * this class are also considered `WC_Query` query vars.
 *
 * @param  array $query_vars
 * @return array
 * @since  2.3.0
 */
function add_woo_query_vars( $query_vars ) {
	return array_merge( $query_vars, [ Core\FRONT_VAR => Core\FRONT_VAR ] );
}

/**
 * Remove installment items from the main subscriptions list.
 *
 * @param  array   $subscriptions  The existing subscriptions.
 * @param  integer $user_id        The user being listed.
 *
 * @return array                   The potentially modified array.
 */
function remove_installments_from_list( $subscriptions, $user_id ) {

	// Immediately bail if this is on the admin side
	// or isn't on the actual account page.
	if ( is_admin() || ! is_account_page() ) {
		return $subscriptions;
	}

	// Allow this filter to be disabled.
	if ( apply_filters( Core\HOOK_PREFIX . 'disable_subscriptions_list_filter', false ) ) {
		return $subscriptions;
	}

	// Now loop and check our meta key.
	foreach ( $subscriptions as $subscription_id => $subscription_obj ) {

		// Remove this one from the overall array.
		if ( 'yes' === sanitize_text_field( get_post_meta( $subscription_id, '_order_has_installments', true ) ) ) {
			unset( $subscriptions[ $subscription_id ] );
		}
	}

	// And return this.
	return $subscriptions;
}

/**
 * Modifies the main query on the WooCommerce products screen to correctly handle filtering by installments.
 *
 * @param  array $query_vars The existing array of query vars for the admin.
 *
 * @return array $query_vars
 */
function modify_installment_product_queries( $query_vars ) {

	// Pull in the globals we need.
	global $pagenow, $typenow;

	// Do our basic check.
	if ( ! is_admin() || 'edit.php' !== $pagenow || 'product' !== $typenow ) {
		return $query_vars;
	}

	// Make sure we have a product type to check against.
	$current_product_type = isset( $_REQUEST['product_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['product_type'] ) ) : false; // phpcs:disable WordPress.Security.NonceVerification.Recommended

	// Bail if we didn't request installments.
	if ( 'installments' !== $current_product_type ) {
		return $query_vars;
	}

	// Now we set the vars to include our meta key
	// and put the product type back to subscriptions.
	$query_vars['meta_value']   = 'yes';
	$query_vars['meta_key']     = '_is_installments';
	$query_vars['product_type'] = [ 'subscription', 'variable-subscription' ];

	// And return the updated vars.
	return $query_vars;
}

/**
 * Modifies the main query on the WooCommerce orders screen to correctly handle filtering by installments.
 *
 * @param  array $query_vars The existing array of query vars for the admin.
 *
 * @return array $query_vars
 */
function modify_installment_order_queries( $query_vars ) {

	// Pull in the globals we need.
	global $pagenow, $typenow, $wpdb;

	// Do our basic check.
	if ( ! is_admin() || 'edit.php' !== $pagenow || 'shop_order' !== $typenow ) {
		return $query_vars;
	}

	// Make sure we have a product type to check against.
	$maybe_sub_type = isset( $_GET['shop_order_subtype'] ) ? sanitize_text_field( wp_unslash( $_GET['shop_order_subtype'] ) ) : false; // phpcs:disable WordPress.Security.NonceVerification.Recommended

	// Bail if we didn't request installments.
	if ( 'installments' !== $maybe_sub_type ) {
		return $query_vars;
	}

	// Now we set the vars to include our meta key and value.
	$query_vars['meta_value'] = 'yes';
	$query_vars['meta_key']   = '_order_has_installments';

	// And return the updated vars.
	return $query_vars;
}
