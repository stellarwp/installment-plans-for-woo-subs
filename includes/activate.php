<?php
/**
 * Our activation call.
 *
 * @package InstallmentPlansWooSubs
 */

// Declare our namespace.
namespace Nexcess\InstallmentPlansWooSubs\Activate;

// Set our aliases.
use Nexcess\InstallmentPlansWooSubs as Core;
use Nexcess\InstallmentPlansWooSubs\Helpers as Helpers;

/**
 * Our inital setup function when activated.
 *
 * @return void
 */
function activate() {

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'before_activate_process' );

	// Do the check for WooCommerce being active.
	check_active_woo();
	check_active_woo_subs();

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'after_activate_process' );

	// Set our initial option flag.
	update_option( Core\OPTION_PREFIX . 'activation_complete', 'no', false );

	// And flush our rewrite rules.
	// We do run this again later, but twice
	// seems to be required.
	flush_rewrite_rules();
}
register_activation_hook( Core\FILE, __NAMESPACE__ . '\activate' );

/**
 * Handle checking if WooCommerce is present and activated.
 *
 * @return void
 */
function check_active_woo() {

	// Pull the function check.
	$maybe_activate = Helpers\maybe_woo_activated();

	// If we weren't false, we are OK.
	if ( false !== $maybe_activate ) {
		return;
	}

	// Deactivate the plugin.
	deactivate_plugins( Core\BASE );

	// And display the notice.
	wp_die( sprintf( __( 'Using the Installment Plans for WooCommerce Subscriptions plugin required that you have WooCommerce installed and activated. <a href="%s">Click here</a> to return to the plugins page.', 'installment-plans-for-woo-subs' ), admin_url( '/plugins.php' ) ) );
}

/**
 * Handle checking if WooCommerce is present and activated.
 *
 * @return void
 */
function check_active_woo_subs() {

	// Pull the function check.
	$maybe_activate = Helpers\maybe_woo_subs_activated();

	// If we weren't false, we are OK.
	if ( false !== $maybe_activate ) {
		return;
	}

	// Deactivate the plugin.
	deactivate_plugins( Core\BASE );

	// And display the notice.
	wp_die( sprintf( __( 'Using the Installment Plans for WooCommerce Subscriptions plugin required that you have WooCommerce Subscriptions installed and activated. <a href="%s">Click here</a> to return to the plugins page.', 'installment-plans-for-woo-subs' ), admin_url( '/plugins.php' ) ) );
}
