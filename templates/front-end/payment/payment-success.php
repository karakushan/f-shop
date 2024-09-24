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
        <p><?php _e( 'Now you can continue shopping.', 'f-shop' ) ?></p>
        <a href="<?php echo esc_url( home_url( '/' ) ) ?>"
           class="fs-btn fs-btn-info"><?php _e( 'Back to home', 'f-shop' ) ?></a>
    </div>
</div>
