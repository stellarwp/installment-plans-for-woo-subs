<?php
/**
 * Any theme specific functionality.
 *
 * @package InstallmentPlansWooSubs
 */

// Declare our namespace.
namespace Nexcess\InstallmentPlansWooSubs\Woo\Themes;

// Set our aliases.
use Nexcess\InstallmentPlansWooSubs as Core;

/**
 * Start our engines.
 */
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_storefront_inline_css', 30 );

/**
 * Load the inline CSS for the sidebar in Storefront.
 */
function load_storefront_inline_css() {

	// Set my CSS.
	$add_theme_icon = 'body.theme-storefront ul li.woocommerce-MyAccount-navigation-link--installment-plans a:before { content: "\f560"; }';

	// Now run it through a filter.
	$set_custom_css = apply_filters( Core\HOOK_PREFIX . 'inline_css', $add_theme_icon );

	// And now load it inline.
	wp_add_inline_style( 'storefront-style', $set_custom_css );
}
