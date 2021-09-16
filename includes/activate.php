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

register_activation_hook( Core\FILE, __NAMESPACE__ . '\activate' );

/**
 * Our inital setup function when activated.
 *
 * @return void
 */
function activate() {

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'before_activate_process' );

	// Do the checks for WooCommerce and Subscriptions both being active.
	check_active( Helpers\maybe_woo_activated() );
	check_active( Helpers\maybe_woo_subs_activated() );

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'after_activate_process' );

	// Set our initial option flag.
	update_option( Core\OPTION_PREFIX . 'activation_complete', 'no', false );

	// And flush our rewrite rules.
	// We do run this again later, but twice
	// seems to be required.
	flush_rewrite_rules();
}

/**
 * Handle checking if WooCommerce is present and activated.
 *
 * @return void
 */
function check_active( $check ) {

	// If we weren't false, we are OK.
	if ( $check ) {
		return;
	}

	// Deactivate the plugin.
	deactivate_plugins( Core\BASE );

	// And display the notice.
	wp_die( wp_kses_post( sprintf(
		/* translators: %1$s: start of link tag, %2$s: end of link tag */
		__( 'Using the Installment Plans for WooCommerce Subscriptions plugin requires that you have WooCommerce installed and activated. %1$Return to the plugins page%2$s.', 'installment-plans-for-woo-subs' ),
		'<a href="' . admin_url( '/plugins.php' ) . '">',
		'</a>'
	) ) );
}
