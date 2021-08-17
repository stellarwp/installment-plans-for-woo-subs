<?php
/**
 * Installments information template (HTML).
 *
 * This is basically copied from the WooCommerce Subscriptions plugin.
 *
 * @package WooInstallmentEmails
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( empty( $subscriptions ) ) {
	return;
}

$has_automatic_renewal = false;
$is_parent_order       = wcs_order_contains_subscription( $order, 'parent' );
?>
<div style="margin-bottom: 40px;">
<h2><?php esc_html_e( 'Installment Plan Information', 'woocommerce-installment-emails' ); ?></h2>
<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 0.5em;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'ID', 'subscription ID table heading', 'woocommerce-installment-emails' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'First Payment', 'table heading', 'woocommerce-installment-emails' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Last Payment', 'table heading', 'woocommerce-installment-emails' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Payment Amount', 'table heading', 'woocommerce-installment-emails' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $subscriptions as $subscription ) : ?>
		<?php $has_automatic_renewal = $has_automatic_renewal || ! $subscription->is_manual(); ?>
		<tr>
			<td class="td" scope="row" style="text-align:left;"><a href="<?php echo esc_url( ( $is_admin_email ) ? wcs_get_edit_post_link( $subscription->get_id() ) : $subscription->get_view_order_url() ); ?>"><?php echo sprintf( esc_html_x( '#%s', 'subscription number in email table. (eg: #106)', 'woocommerce-installment-emails' ), esc_html( $subscription->get_order_number() ) ); ?></a></td>

			<td class="td" scope="row" style="text-align:left;"><?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'start_date', 'site' ) ) ); ?></td>

			<td class="td" scope="row" style="text-align:left;"><?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) ); ?></td>

			<td class="td" scope="row" style="text-align:left;">
				<?php echo wp_kses_post( $subscription->get_formatted_order_total() ); ?>
				<?php if ( $is_parent_order && $subscription->get_time( 'next_payment' ) > 0 ) : ?>
					<br>
					<small><?php printf( esc_html__( 'Next payment: %s', 'woocommerce-installment-emails' ), esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) ) ); ?></small>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
</tbody>
</table>
<?php if ( $has_automatic_renewal && ! $is_admin_email && $subscription->get_time( 'next_payment' ) > 0 ) {
	if ( count( $subscriptions ) === 1 ) {
		$subscription   = reset( $subscriptions );
		$my_account_url = $subscription->get_view_order_url();
	} else {
		$my_account_url = wc_get_endpoint_url( 'subscriptions', '', wc_get_page_permalink( 'myaccount' ) );
	}

	// Translators: Placeholders are opening and closing My Account link tags.
	printf( '<small>%s</small>', wp_kses_post( sprintf( _n(
		'This installment plan is set to renew automatically using your payment method on file. You can manage or cancel this order from your %smy account page%s.',
		'These installment plans are set to renew automatically using your payment method on file. You can manage or cancel your orders from your %smy account page%s.',
		count( $subscriptions ),
		'woocommerce-installment-emails'
	), '<a href="' . $my_account_url . '">', '</a>' ) ) );
}?>
</div>

