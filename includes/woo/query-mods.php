<?php
/**
 * Our functions related to modifying queries "my account" page.
 *
 * Set up the actions that happen inside the admin area.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Woo\QueryMods;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;

/**
 * Start our engines.
 */
add_action( 'init', __NAMESPACE__ . '\add_account_rewrite_endpoint' );
add_filter( 'query_vars', __NAMESPACE__ . '\add_account_endpoint_vars', 0 );
add_filter( 'woocommerce_get_query_vars', __NAMESPACE__ . '\add_woo_query_vars' );

/**
 * Register new endpoint to use inside My Account page.
 *
 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
 */
function add_account_rewrite_endpoint() {
	add_rewrite_endpoint( Core\FRONT_VAR, EP_ROOT | EP_PAGES );
}

/**
 * Add new query var for the installments endpoint.
 *
 * @param  array $vars  The existing query vars.
 *
 * @return array
 */
function add_account_endpoint_vars( $vars ) {

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
	return array_merge( $query_vars, array( Core\FRONT_VAR => Core\FRONT_VAR ) );
}
