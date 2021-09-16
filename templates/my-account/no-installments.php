<?php
/**
 * My installment plans section on the My Account page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="woocommerce_account_subscriptions">

	<p class="no_subscriptions woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<?php esc_html_e( 'You have no active installment plans.', 'installment-plans-for-woo-subs' ); ?>
		<a class="woocommerce-Button button" href="<?php echo esc_url( $no_items_link ); ?>"><?php echo esc_html( $no_items_text ); ?></a>
	</p>

</div>
