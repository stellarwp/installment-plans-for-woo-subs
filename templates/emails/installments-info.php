<?php
/**
 * Installments information template (HTML).
 *
 * This was copied from the WooCommerce Subscriptions
 * extension and then modified to fit our needs.
 *
 * @package InstallmentPlansWooSubs
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( empty( $subscriptions ) ) {
	return;
}
?>

<div style="margin-bottom: 40px;">
	<h2>
		<?php esc_html_e( 'Payment Plan Information', 'installment-plans-for-woo-subs' ); ?>
	</h2>

	<table class="td" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 0.5em; border-width: 1px;">

		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:left;">
					<?php echo esc_html_x( 'Installment', 'table heading', 'installment-plans-for-woo-subs' ); ?>
				</th>

				<th class="td" scope="col" style="text-align:left;">
					<?php echo esc_html_x( 'Plan Details', 'table heading', 'installment-plans-for-woo-subs' ); ?>
				</th>

				<th class="td" scope="col" style="text-align:left;">
					<?php echo esc_html_x( 'Next Payment', 'table heading', 'installment-plans-for-woo-subs' ); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ( $subscriptions as $subscription ) : ?>
			<?php $content_args = wcsip_get_email_content_args( $subscription, $order ); // Get my content. ?>

			<tr>
				<td class="td" style="text-align:left;">
					<?php echo wp_kses_post( $content_args['payment-detail'] ); ?><br>

					<?php
					if ( $is_admin_email ) {
						$url = wcs_get_edit_post_link( $subscription->get_id() );
					} else {
						$url = $subscription->get_view_order_url();
					}
					?>

					<a href="<?php echo esc_url( ( $url ) ); ?>">
						<?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'date_created', 'site' ) ) ); ?>
					</a>
				</td>

				<td class="td" style="text-align:left;">
					<strong><?php echo wp_kses_post( $content_args['payment-counts'] ); ?></strong><br>

					<?php echo wp_kses_post( $content_args['payment-schedule'] ); ?><br>

					(<?php echo wp_kses_post( $content_args['payment-totals'] ); ?>)
				</td>

				<td class="td" style="text-align:left;">

					<?php
					if ( $subscription->get_time( 'next_payment' ) > 0 ) {
						echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) );
					} else {
						echo wp_kses_post( $content_args['no-remaining'] );
					}
					?>

				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
	</table>
	<?php
	if ( ! $subscription->is_manual() && ! $is_admin_email && ( $subscription->get_time( 'next_payment' ) > 0 ) ) {
		if ( 1 === count( $subscriptions ) ) {
			$subscription   = reset( $subscriptions );
			$my_account_url = $subscription->get_view_order_url();
		} else {
			$my_account_url = wc_get_endpoint_url( 'subscriptions', '', wc_get_page_permalink( 'myaccount' ) );
		}

		echo '<small>';

		/* Translators: Placeholders are opening and closing My Account link tags. */
		wp_kses_post( sprintf( _n(
			'This installment plan is set to renew automatically using your payment method on file. You can manage or cancel this order from your %1$smy account page%2$s.',
			'These installment plans are set to renew automatically using your payment method on file. You can manage or cancel your orders from your %1$smy account page%2$s.',
			count( $subscriptions ),
			'installment-plans-for-woo-subs'
		), '<a href="' . $my_account_url . '">', '</a>' ) );

		echo '</small>';
	}
	?>
</div>
