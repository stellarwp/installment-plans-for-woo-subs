<?php
/**
 * Our helper functions to use across the plugin.
 *
 * @package WooInstallmentEmails
 */

// Declare our namespace.
namespace Nexcess\WooInstallmentEmails\Helpers;

// Set our aliases.
use Nexcess\WooInstallmentEmails as Core;
use Nexcess\WooInstallmentEmails\Utilities as Utilities;

/**
 * Check to see if WooCommerce is installed and active.
 *
 * @return boolean
 */
function maybe_woo_activated() {
	return class_exists( 'woocommerce' ) ? true : false;
}
