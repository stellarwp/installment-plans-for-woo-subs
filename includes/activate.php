<?php
/**
 * Our activation call.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Activate;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;

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

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'after_activate_process' );

	// And flush our rewrite rules.
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
	$maybe_activate_woo = Helpers\maybe_woo_activated();

	// If we weren't false, we are OK.
	if ( false !== $maybe_activate_woo ) {
		return;
	}

	// Deactivate the plugin.
	deactivate_plugins( Core\BASE );

	// And display the notice.
	wp_die( sprintf( __( 'Using the WooCommerce Sales Performance Monitor plugin required that you have WooCommerce installed and activated. <a href="%s">Click here</a> to return to the plugins page.', 'woo-sales-performance-monitor' ), admin_url( '/plugins.php' ) ) );
}
