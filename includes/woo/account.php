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
add_action( 'init', __NAMESPACE__ . '\add_account_rewrite_endpoint' );
add_filter( 'query_vars', __NAMESPACE__ . '\add_account_endpoint_vars', 0 );
// add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_endpoint_assets' );
add_filter( 'the_title', __NAMESPACE__ . '\add_endpoint_title' );
add_action( 'woocommerce_before_account_navigation', __NAMESPACE__ . '\add_endpoint_notices', 15 );
add_filter( 'woocommerce_account_menu_items', __NAMESPACE__ . '\add_endpoint_menu_item' );
add_action( 'woocommerce_account_installment-plans_endpoint', __NAMESPACE__ . '\add_endpoint_content' );

/**
 * Register new endpoint to use inside My Account page.
 *
 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
 */
function add_account_rewrite_endpoint() {
	add_rewrite_endpoint( Core\FRONT_VAR, EP_ROOT | EP_PAGES );
}

/**
 * Add new query var for the GDPR endpoint.
 *
 * @param  array $vars  The existing query vars.
 *
 * @return array
 */
function add_account_endpoint_vars( $vars ) {

	// Add our new endpoint var if we don't already have it.
	if ( ! in_array( Core\FRONT_VAR, $vars ) ) {
		$vars[] = Core\FRONT_VAR;
	}

	// And return it.
	return $vars;
}

/**
 * Load our front-end side JS and CSS.
 *
 * @return void
 */
function load_endpoint_assets() {

	// Bail if we aren't on the right general place.
	if ( ! Helpers\maybe_account_endpoint_page() ) {
		return;
	}

	// Set my handle.
	$handle = 'nx-wc-installments-account';

	// Set a file suffix structure based on whether or not we want a minified version.
	$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? $handle : $handle . '.min';

	// Set a version for whether or not we're debugging.
	$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : Core\VERS;

	// Load our CSS file.
	wp_enqueue_style( $handle, Core\ASSETS_URL . '/css/' . $file . '.css', false, $vers, 'all' );

	// And our JS.
	wp_enqueue_script( $handle, Core\ASSETS_URL . '/js/' . $file . '.js', array( 'jquery' ), $vers, true );
}

/**
 * Set a title for the individual endpoint we just made.
 *
 * @param  string $title  The existing page title.
 *
 * @return string
 */
function add_endpoint_title( $title ) {

	// Bail if we aren't on the page.
	if ( ! Helpers\maybe_account_endpoint_page( true ) ) {
		return $title;
	}

	// Set our new page title.
	$page_title = apply_filters( Core\HOOK_PREFIX . 'endpoint_page_title', __( 'My Installment Plans', 'woocommerce-installment-emails' ), $title );

	// Remove the filter so we don't loop endlessly.
	remove_filter( 'the_title', __NAMESPACE__ . '\add_endpoint_title' );

	// Return the title.
	return $page_title;
}

/**
 * Add the notices above the "my account" area.
 *
 * @return HTML
 */
function add_endpoint_notices() {

	// Bail if we aren't on the right general place.
	if ( ! Helpers\maybe_account_endpoint_page() ) {
		return;
	}

	// Bail without our result flag.
	if ( empty( $_GET['nx-installments-action'] ) ) {

		// Echo out the blank placeholder for Ajax calls.
		echo '<div class="nx-woo-installment-plans-notices"></div>';

		// And just be done.
		return;
	}

	/*
	// Check for a response code.
	$msg_code   = ! empty( $_GET['errcode'] ) ? sanitize_text_field( $_GET['errcode'] ) : 'unknown';

	// Figure out the text.
	$msg_text   = ! empty( $_GET['message'] ) ? sanitize_text_field( $_GET['message'] ) : Helpers\notice_text( $msg_code );

	// Determine the message type.
	$msg_type   = empty( $_GET['success'] ) ? 'error' : 'success';

	// Output the message.
	echo Layouts\account_message_markup( $msg_text, $msg_type, true, false ); // WPCS: XSS ok.
	*/
}

/**
 * Merge in our new enpoint into the existing "My Account" menu.
 *
 * @param  array $items  The existing menu items.
 *
 * @return array
 */
function add_endpoint_menu_item( $items ) {

	// Bail if no installment plans exist at all.
	if ( ! Helpers\maybe_store_has_installments() ) {
		return $items;
	}

	// Set up our menu item title.
	$menu_title = apply_filters( Core\HOOK_PREFIX . 'endpoint_menu_title', __( 'Installment Plans', 'woocommerce-installment-emails' ), $items );

	// Add it to the array.
	$set_items  = wp_parse_args( array( Core\FRONT_VAR => esc_attr( $menu_title ) ), $items );

	// Return our tabs.
	return Helpers\adjust_account_tab_order( $set_items );
}

/**
 * Add the content for our endpoint to display.
 *
 * @return HTML
 */
function add_endpoint_content() {
	echo '<p>things!</p>';
}
