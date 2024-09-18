<?php
/**
 * @var int $order_id - id заказа
 * @var WP_Term $term - способ оплаты
 * @var string $pay_button - кнопка оплаты
 * @var float $order_amount - сумма заказа
 */
?>
<div class="fs-action-message fs-action-info">
    <div class="fs-action-message__left">
        <img decoding="async" src="<?php echo esc_url( FS_PLUGIN_URL . 'assets/img/icon/pay.svg' ) ?>"
             alt="icon">
    </div>
    <div class="fs-action-message__right">
        <h4><?php printf( __( 'Payment for order #%d with &laquo;%s&raquo;', 'f-shop' ), $order_id, $term->name ) ?></h4>
        <p><?php printf( __( 'If payment is successful, you will be charged %s %s.', 'f-shop' ), apply_filters( 'fs_price_format', $order_amount ), fs_currency() ) ?></p>
		<?php echo $pay_button ?>
    </div>
</div>
