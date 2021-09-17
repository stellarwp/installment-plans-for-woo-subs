<?php
/**
 * Our uninstall call.
 *
 * @package InstallmentPlansWooSubs
 */

// Declare our namespace.
namespace Nexcess\InstallmentPlansWooSubs\Uninstall;

// Set our aliases.
use Nexcess\InstallmentPlansWooSubs as Core;

/**
 * Delete various options when uninstalling the plugin.
 */
function uninstall() {

	// Delete the activation flag option.
	delete_option( Core\OPTION_PREFIX . 'activation_complete' );

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'before_uninstall_process' );

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'after_uninstall_process' );

	// And flush our rewrite rules.
	flush_rewrite_rules();
}
register_uninstall_hook( Core\FILE, __NAMESPACE__ . '\uninstall' );
