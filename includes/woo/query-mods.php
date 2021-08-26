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
add_action( 'init', __NAMESPACE__ . '\add_installments_rewrite_endpoint' );
add_filter( 'query_vars', __NAMESPACE__ . '\add_installments_endpoint_vars', 0 );
add_filter( 'woocommerce_get_query_vars', __NAMESPACE__ . '\add_woo_query_vars' );
add_filter( 'wcs_get_users_subscriptions', __NAMESPACE__ . '\remove_installments_from_list', 20, 2 );

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
	return array_merge( $query_vars, array( Core\FRONT_VAR => Core\FRONT_VAR ) );
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
    if ( false !== apply_filters( Core\HOOK_PREFIX . 'disable_subscriptions_list_filter', false ) ) {
        return $subscriptions;
    }

    // Return the empty array if that is what we were provided.
    if ( empty( $subscriptions ) ) {
        return $subscriptions;
    }

    // Now loop and check our meta key.
    foreach ( $subscriptions as $subscription_id => $subscription_obj ) {

        // Check the meta.
        $maybe_has  = get_post_meta( $subscription_id, '_order_has_installments', true );

        // Skip if it isn't in the array.
        if ( empty( $maybe_has ) || 'yes' !== sanitize_text_field( $maybe_has ) ) {
            continue;
        }

        // Remove this one from the overall array.
        unset( $subscriptions[ $subscription_id ] );
    }

    // And return this.
    return $subscriptions;
}
