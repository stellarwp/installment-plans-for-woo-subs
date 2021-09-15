<?php
/**
 * Our deactivation call.
 *
 * @package InstallmentPlansWooSubs
 */

// Declare our namespace.
namespace Nexcess\InstallmentPlansWooSubs\Deactivate;

// Set our aliases.
use Nexcess\InstallmentPlansWooSubs as Core;

/**
 * Delete various options when deactivating the plugin.
 *
 * @return void
 */
function deactivate() {

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'before_deactivate_process' );

	// Include our action so that we may add to this later.
	do_action( Core\HOOK_PREFIX . 'after_deactivate_process' );

	// And flush our rewrite rules.
	flush_rewrite_rules();
}
register_deactivation_hook( Core\FILE, __NAMESPACE__ . '\deactivate' );
