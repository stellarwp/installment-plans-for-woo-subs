<?php
/**
 * Plugin Name: Installment Plans for WooCommerce Subscriptions
 * Plugin URI:  https://www.nexcess.net
 * Description: Extend the Subscriptions plugin for WooCommerce to handle installments.
 * Version:     1.0.0
 * Author:      Nexcess
 * Author URI:  https://www.nexcess.net
 * Text Domain: installment-plans-for-woo-subs
 * Domain Path: /languages
 * WC requires at least: 5.5.0
 * WC tested up to: 5.6.0
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package InstallmentPlansWooSubs
 */

// Declare our namespace.
namespace Nexcess\InstallmentPlansWooSubs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our plugin version.
define( __NAMESPACE__ . '\VERS', '1.0.0' );

// Plugin root file.
define( __NAMESPACE__ . '\FILE', __FILE__ );

// Define our file base.
define( __NAMESPACE__ . '\BASE', plugin_basename( __FILE__ ) );

// Plugin Folder URL.
define( __NAMESPACE__ . '\URL', plugin_dir_url( __FILE__ ) );

// Set our includes and template path constants.
define( __NAMESPACE__ . '\INCLUDES_PATH', __DIR__ . '/includes' );
define( __NAMESPACE__ . '\TEMPLATES_PATH', __DIR__ . '/templates' );

// Set the various prefixes for our actions and filters.
define( __NAMESPACE__ . '\HOOK_PREFIX', 'wcs_installment_plans_' );
define( __NAMESPACE__ . '\OPTION_PREFIX', 'wcsip_option_' );
define( __NAMESPACE__ . '\TRANSIENT_PREFIX', 'wcsip_tr_' );

// Set our front menu endpoint constant.
define( __NAMESPACE__ . '\FRONT_VAR', 'installment-plans' );

// Now we handle all the various file loading.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_files' );

/**
 * Actually load our files.
 */
function load_files() {
	// Load the multi-use files first.
	require_once __DIR__ . '/includes/helpers.php';
	require_once __DIR__ . '/includes/utilities.php';

	// Load the Woo related files.
	require_once __DIR__ . '/includes/woo/query-mods.php';
	require_once __DIR__ . '/includes/woo/meta.php';
	require_once __DIR__ . '/includes/woo/orders.php';
	require_once __DIR__ . '/includes/woo/email.php';
	require_once __DIR__ . '/includes/woo/account.php';
	require_once __DIR__ . '/includes/woo/admin.php';
	require_once __DIR__ . '/includes/woo/themes.php';

	// Load the triggered file loads.
	require_once __DIR__ . '/includes/activate.php';
	require_once __DIR__ . '/includes/deactivate.php';
	require_once __DIR__ . '/includes/uninstall.php';
}
