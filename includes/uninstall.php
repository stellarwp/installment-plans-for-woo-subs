<?php
/**
 * Our uninstall call.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Uninstall;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;

/**
 * Delete various options when uninstalling the plugin.
 *
 * @return void
 */
function uninstall() {

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'before_uninstall_process' );

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'after_uninstall_process' );

	// And flush our rewrite rules.
	flush_rewrite_rules();
}
register_uninstall_hook( Core\FILE, __NAMESPACE__ . '\uninstall' );
