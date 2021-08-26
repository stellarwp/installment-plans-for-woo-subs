<?php
/**
 * Plugin Name: WooCommerce Installment Emails
 * Plugin URI:  https://www.nexcess.net
 * Description: Filter the email content for subscriptions as installments.
 * Version:     0.0.1-dev
 * Author:      Nexcess
 * Author URI:  https://www.nexcess.net
 * Text Domain: woocommerce-installment-emails
 * Domain Path: /languages
 * WC requires at least: 5.2.0
 * WC tested up to: 5.3.0
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our plugin version.
define( __NAMESPACE__ . '\VERS', '0.0.1-dev' );

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
define( __NAMESPACE__ . '\HOOK_PREFIX', 'wc_installment_emails_' );
define( __NAMESPACE__ . '\NONCE_PREFIX', 'woo_insteml_nonce_' );
define( __NAMESPACE__ . '\TRANSIENT_PREFIX', 'wcinsteml_tr_' );
define( __NAMESPACE__ . '\OPTION_PREFIX', 'woo_insteml_setting_' );

// Set our front menu endpoint constant.
define( __NAMESPACE__ . '\FRONT_VAR', 'installment-plans' );

// Now we handle all the various file loading.
nx_woo_installment_emails_file_load();

/**
 * Actually load our files.
 *
 * @return void
 */
function nx_woo_installment_emails_file_load() {

	// Load the multi-use files first.
	require_once __DIR__ . '/includes/helpers.php';
	require_once __DIR__ . '/includes/utilities.php';

	// Load the Woo related files.
	require_once __DIR__ . '/includes/woo/query-mods.php';
    require_once __DIR__ . '/includes/woo/meta.php';
	require_once __DIR__ . '/includes/woo/orders.php';
	require_once __DIR__ . '/includes/woo/email.php';
	require_once __DIR__ . '/includes/woo/account.php';

	// Load the triggered file loads.
	require_once __DIR__ . '/includes/activate.php';
	require_once __DIR__ . '/includes/deactivate.php';
	require_once __DIR__ . '/includes/uninstall.php';
}
