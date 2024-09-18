<?php
/**
 * @var int $order_id - id заказа
 */
?>
<div class="fs-action-message fs-action-info">
    <div class="fs-action-message__left">
        <img decoding="async" src="<?php echo esc_url( FS_PLUGIN_URL . 'assets/img/icon/pay.svg' ) ?>"
             alt="icon">
    </div>
    <div class="fs-action-message__right">
        <h4><?php printf( __( 'Order #%d paid successfully', 'f-shop' ), $order_id ) ?></h4>
        <p><?php _e( 'You or someone else paid for this order.', 'f-shop' ) ?></p>
    </div>
</div>
