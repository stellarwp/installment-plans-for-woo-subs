<?php
/**
 * Our functions related to the "my account" page.
 *
 * Set up the actions that happen inside the admin area.
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
add_filter( 'the_title', __NAMESPACE__ . '\change_endpoint_title', 11, 1 );
add_filter( 'woocommerce_account_menu_items', __NAMESPACE__ . '\add_endpoint_menu_item' );
add_filter( 'woocommerce_account_menu_item_classes', __NAMESPACE__ . '\maybe_add_active_class', 10, 2 );
add_filter( 'woocommerce_endpoint_installment-plans_title', __NAMESPACE__ . '\change_list_view_title', 10, 3 );
add_filter( 'woocommerce_endpoint_view-subscription_title', __NAMESPACE__ . '\change_single_view_title', 30, 3 );
add_action( 'woocommerce_account_installment-plans_endpoint', __NAMESPACE__ . '\add_endpoint_content' );

/**
 * Merge in our new enpoint into the existing "My Account" menu.
 *
 * @param  array $menu_items  The existing menu items.
 *
 * @return array              The (possibly) modified menu items.
 */
function add_endpoint_menu_item( $menu_items ) {

	// Bail if no installment plans exist at all.
	if ( ! Helpers\maybe_store_has_installments() ) {
		return $menu_items;
	}

	// Set up our menu item title.
	$menu_title = apply_filters( Core\HOOK_PREFIX . 'endpoint_menu_title', __( 'Installment Plans', 'installment-plans-for-woo-subs' ), $menu_items );

	// Add our menu item after the Subscription tab if it exists.
	if ( array_key_exists( 'subscriptions', $menu_items ) ) {
		return wcsip_array_insert_after( 'subscriptions', $menu_items, Core\FRONT_VAR, $menu_title );
	}

	// Add our menu item after the Orders tab if it exists.
	if ( array_key_exists( 'orders', $menu_items ) ) {
		return wcsip_array_insert_after( 'orders', $menu_items, Core\FRONT_VAR, $menu_title );
	}

	// Add our menu item after the Logout tab if it exists.
	if ( array_key_exists( 'customer-logout', $menu_items ) ) {
		return wcsip_array_insert_after( 'customer-logout', $menu_items, Core\FRONT_VAR, $menu_title );
	}

	// None existed, just throw it on the end.
	return wp_parse_args( array( Core\FRONT_VAR => esc_attr( $menu_title ) ), $menu_items );
}

/**
 * Adds `is-active` class to Subscriptions label when we're viewing a single Subscription.
 *
 * @param  array  $classes   The classes present in the current endpoint.
 * @param  string $endpoint  The endpoint/label we're filtering.
 *
 * @return array             The (possibly) modified class items.
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
 * Changes page title on view subscription page.
 *
 * @param  string $title  Our original title.
 *
 * @return string         Our (possibly) changed title.
 */
function change_endpoint_title( $title ) {

	// We only wanna do this in the proper loop.
	if ( in_the_loop() && is_account_page() ) {

		// Call the global query object.
		global $wp_query;

		// Change the title if we have our var.
		if ( isset( $wp_query->query_vars[ Core\FRONT_VAR ] ) ) {

			// Set the title with a filter.
			$title = apply_filters( Core\HOOK_PREFIX . 'endpoint_page_title', __( 'My Installment Plans', 'installment-plans-for-woo-subs' ) );

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
 * @return string            Our title for the endpoint.
 */
function change_list_view_title( $title, $endpoint, $action ) {

	// Return ours, filtered.
	return apply_filters( Core\HOOK_PREFIX . 'endpoint_page_title', __( 'Installment Plans', 'installment-plans-for-woo-subs' ), $title, $action );
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
 * @return string            Our title for the endpoint.
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
		$title = sprintf( _x( 'Installment Plan #%s', 'hash before order number', 'installment-plans-for-woo-subs' ), $is_single_subscription->get_order_number() );
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
	$get_installments   = wcsip_get_user_installments();

	// Set our template name.
	$set_template_name  = ! empty( $get_installments ) ? 'my-account/installments-list.php' : 'my-account/no-installments.php';

	// Set up our args in an array.
	$set_template_args  = array(
		'name' => $set_template_name,
		'path' => Core\TEMPLATES_PATH . '/'
	);

	// Run it through a filter for others to change.
	$set_template_args  = apply_filters( Core\HOOK_PREFIX . 'endpoint_page_content_template_args', $set_template_args, $get_installments );

	// Set the text for "no installment plans".
	$set_emptyitem_link = apply_filters( Core\HOOK_PREFIX . 'no_installments_return_link', wc_get_page_permalink( 'shop' ) );
	$set_emptyitem_text = apply_filters( Core\HOOK_PREFIX . 'no_installments_return_text', __( 'Browse products', 'installment-plans-for-woo-subs' ) );

	// Return the WC template setup.
	wc_get_template(
		$set_template_args['name'],
		array(
			'installments'  => $get_installments,
			'no_items_link' => $set_emptyitem_link,
			'no_items_text' => $set_emptyitem_text,
		),
		'',
		$set_template_args['path']
	);

	// Nothing left.
}
