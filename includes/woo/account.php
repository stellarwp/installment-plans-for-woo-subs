<?php
/**
 * Our functions related to the "my account" page.
 *
 * Set up the actions that happen inside the admin area.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Woo\Emails;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;
use Nexcess\WooInstallmentEmails\Helpers as Helpers;
use Nexcess\WooInstallmentEmails\Utilities as Utilities;

/**
 * Start our engines.
 */
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_storefront_inline_css', 30 );
add_filter( 'the_title', __NAMESPACE__ . '\change_endpoint_title', 11, 1 );
add_filter( 'woocommerce_account_menu_items', __NAMESPACE__ . '\add_endpoint_menu_item' );
add_filter( 'woocommerce_account_menu_item_classes', __NAMESPACE__ . '\maybe_add_active_class', 10, 2 );
add_filter( 'woocommerce_endpoint_installment-plans_title', __NAMESPACE__ . '\change_list_view_title', 10, 3 );
add_filter( 'woocommerce_endpoint_view-subscription_title', __NAMESPACE__ . '\change_single_view_title', 30, 3 );
add_action( 'woocommerce_account_installment-plans_endpoint', __NAMESPACE__ . '\add_endpoint_content' );
add_filter( 'wcs_get_users_subscriptions', __NAMESPACE__ . '\remove_installments_from_list', 20, 2 );

/**
 * Load the inline CSS for the sidebar in Storefront.
 *
 * @return void
 */
function load_storefront_inline_css() {

	// Set my CSS.
	$add_theme_icon = 'body.theme-storefront ul li.woocommerce-MyAccount-navigation-link--installment-plans a:before { content: "\f560"; }';

	// Now run it through a filter.
	$set_custom_css = apply_filters( Core\HOOK_PREFIX . 'inline_css', $add_theme_icon );

	// And now load it inline.
	wp_add_inline_style( 'storefront-style', $set_custom_css );
}

/**
 * Merge in our new enpoint into the existing "My Account" menu.
 *
 * @param  array $menu_items  The existing menu items.
 *
 * @return array
 */
function add_endpoint_menu_item( $menu_items ) {

	// Bail if no installment plans exist at all.
	if ( ! Helpers\maybe_store_has_installments() ) {
		return $menu_items;
	}

	// Set up our menu item title.
	$menu_title = apply_filters( Core\HOOK_PREFIX . 'endpoint_menu_title', __( 'Installment Plans', 'woocommerce-installment-emails' ), $menu_items );

	// Add our menu item after the Subscription tab if it exists.
	if ( array_key_exists( 'subscriptions', $menu_items ) ) {
		return wcie_array_insert_after( 'subscriptions', $menu_items, Core\FRONT_VAR, $menu_title );
	}

	// Add our menu item after the Orders tab if it exists.
	if ( array_key_exists( 'orders', $menu_items ) ) {
		return wcie_array_insert_after( 'orders', $menu_items, Core\FRONT_VAR, $menu_title );
	}

	// Add our menu item after the Logout tab if it exists.
	if ( array_key_exists( 'customer-logout', $menu_items ) ) {
		return wcie_array_insert_after( 'customer-logout', $menu_items, Core\FRONT_VAR, $menu_title );
	}

	// None existed, just throw it on the end.
	return wp_parse_args( array( Core\FRONT_VAR => esc_attr( $menu_title ) ), $menu_items );
}

/**
 * Adds `is-active` class to Subscriptions label when we're viewing a single Subscription.
 *
 * @param array  $classes  The classes present in the current endpoint.
 * @param string $endpoint The endpoint/label we're filtering.
 *
 * @return array
 * @since 2.5.6
 */
function maybe_add_active_class( $classes, $endpoint ) {

	// Bail if we aren't on the right general place.
	if ( ! Helpers\maybe_installments_endpoint_page() ) {
		return $classes;
	}

	// Throw our active on there if it matches up.
	if ( ! isset( $classes['is-active'] ) && Core\FRONT_VAR === $endpoint ) {
		$classes[] = 'is-active';
	}

	// Return the resulting array.
	return $classes;
}

/**
 * Changes page title on view subscription page
 *
 * @param  string $title original title
 * @return string        changed title
 */
function change_endpoint_title( $title ) {

	// We only wanna do this in the proper loop.
	if ( in_the_loop() && is_account_page() ) {

		// Call the global query object.
		global $wp_query;

		// Change the title if we have our var.
		if ( isset( $wp_query->query_vars[ Core\FRONT_VAR ] ) ) {

			// Set the title with a filter.
			$title = apply_filters( Core\HOOK_PREFIX . 'endpoint_page_title', __( 'My Installment Plans', 'woocommerce-installment-emails' ) );

			// Unhook after we've returned our title to prevent it from overriding others.
			remove_filter( 'the_title', __NAMESPACE__ . '\change_endpoint_title', 11 );
		}

		// Nothing left inside this check.
	}

	// Now return the title.
	return $title;
}

/**
 * Hooks onto `woocommerce_endpoint_{$endpoint}_title` to return the correct page title for the installment endpoints
 * in My Account.
 *
 * @param  string $title     Default title.
 * @param  string $endpoint  Endpoint key.
 * @param  string $action    Optional action or variation within the endpoint.
 *
 * @return string
 */
function change_list_view_title( $title, $endpoint, $action ) {

	// Return ours, filtered.
	return apply_filters( Core\HOOK_PREFIX . 'endpoint_page_title', __( 'Installment Plans', 'woocommerce-installment-emails' ), $title, $action );
}

/**
 * Hooks onto `woocommerce_endpoint_{$endpoint}_title` to return the correct page title for an individual installment
 * in My Account.
 *
 * We run this after Subscriptions does so we can change the ones that apply to us.
 *
 * @param  string $title     Default title.
 * @param  string $endpoint  Endpoint key.
 * @param  string $action    Optional action or variation within the endpoint.
 *
 * @return string
 */
function change_single_view_title( $title, $endpoint, $action ) {

	// Call the global.
	global $wp;

	// Check for a subscription.
	$is_single_subscription = wcs_get_subscription( $wp->query_vars['view-subscription'] );

	// If we don't have it, return whatever it was.
	if ( empty( $is_single_subscription ) ) {
		return $title;
	}

	// Check for the installment being there.
	$maybe_has_installments = Helpers\maybe_order_has_installments( $is_single_subscription, false );

	// If we have installments, swap our title.
	if ( false !== $maybe_has_installments ) {
		// translators: placeholder is a subscription ID.
		$title = sprintf( _x( 'Installment Plan #%s', 'hash before order number', 'woocommerce-installment-emails' ), $is_single_subscription->get_order_number() );
	}

	// Return the title.
	return $title;
}

/**
 * Add the content for our endpoint to display.
 *
 * @return HTML
 */
function add_endpoint_content() {

	// Get the installments.
	$get_installments   = wcie_get_user_installments();

	// Set our template name.
	$set_template_name  = ! empty( $get_installments ) ? 'my-account/installments-list.php' : 'my-account/no-installments.php';

	// Return the WC template setup.
	wc_get_template(
		$set_template_name,
		array( 'installments' => $get_installments ),
		'',
		Core\TEMPLATES_PATH . '/'
	);

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
