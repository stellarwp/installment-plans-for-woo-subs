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
<h2><?php esc_html_e( 'Payment Plan Information', 'woocommerce-installment-emails' ); ?></h2>
<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 0.5em;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Installment', 'table heading', 'woocommerce-installment-emails' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Plan Details', 'table heading', 'woocommerce-installment-emails' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Next Payment', 'table heading', 'woocommerce-installment-emails' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $subscriptions as $subscription ) : ?>
		<?php $has_automatic_renewal = $has_automatic_renewal || ! $subscription->is_manual(); ?>

		<?php $content_args = wc_installment_emails_get_content_args( $subscription, $order ); // Get my content. ?>

		<tr>
			<td class="td" scope="row" style="text-align:left;">
				<?php echo wp_kses_post( $content_args['payment-detail'] ); ?><br>
				<a href="<?php echo esc_url( ( $is_admin_email ) ? wcs_get_edit_post_link( $subscription->get_id() ) : $subscription->get_view_order_url() ); ?>"><?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'date_created', 'site' ) ) ); ?></a>
			</td>

			<td class="td" scope="row" style="text-align:left;">
				<strong><?php echo wp_kses_post( $content_args['payment-counts'] ); ?></strong><br>
				<?php echo wp_kses_post( $content_args['payment-schedule'] ); ?><br>
				(<?php echo wp_kses_post( $content_args['total-cost'] ); ?>)
			</td>

			<td class="td" scope="row" style="text-align:left;">

				<?php
				if ( $subscription->get_time( 'next_payment' ) > 0 ) :
					echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) );
				else :
					echo wp_kses_post( $content_args['no-remaining'] );
				endif;
				?>

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

