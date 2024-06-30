<?php if ( empty( $order ) || ! is_object( $order ) ) {
	exit();
} ?>
<div class="fs-order-detail order-detail">
    <p>
        <a href="<?php echo esc_url( remove_query_arg( 'order_detail' ) ) ?>"
           class="btn btn-primary"><i class="fas fa-angle-left"></i> <?php esc_html_e( 'Back to orders', 'f-shop' ); ?></a>
    </p>
    <div class="order-detail-title"><?php echo esc_html( sprintf( __( 'Order details #%d', 'f-shop' ), intval( $order->ID ) ) ); ?></div>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <td><?php esc_html_e( '#ID', 'f-shop' ); ?></td>
                <td><?php esc_html_e( 'Photo', 'f-shop' ); ?></td>
                <td><?php esc_html_e( 'Title', 'f-shop' ); ?></td>
                <td><?php esc_html_e( 'Price', 'f-shop' ); ?></td>
                <td><?php esc_html_e( 'Qty', 'f-shop' ); ?></td>
                <td><?php esc_html_e( 'Cost', 'f-shop' ); ?></td>
            </tr>
            </thead>
            <tbody>
			<?php
			if ( ! empty( $order->data->_products ) ): ?>
				<?php foreach ( $order->data->_products as $id => $item ):
					$product = fs_set_product( $item );
					?>
                    <tr>
                        <td><?php echo esc_html( $product->id ) ?></td>
                        <td class="thumb"><?php fs_product_thumbnail( $item->ID ) ?></td>
                        <td><a href="<?php echo esc_url( $product->permalink ) ?>"
                               target="_blank"><?php echo esc_html( $product->title ) ?></a></td>
                        <td><?php echo esc_html( $product->price_display ) ?>
                            &nbsp; <?php echo esc_html( $product->currency ) ?><br>
                            <del><?php echo esc_html( $product->base_price_display ) ?>
                                &nbsp; <?php echo esc_html( $product->currency ) ?></del>
                        </td>
                        <td><?php echo esc_html( $product->count ) ?></td>
                        <td><?php echo esc_html( $product->cost_display ) ?>
                            &nbsp; <?php echo esc_html( $product->currency ) ?></td>
                    </tr>
				<?php endforeach; ?>
			<?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5"><?php esc_html_e( 'Total cost', 'f-shop' ); ?></td>
                <td><?php echo esc_html( $order->data->_amount ) ?>&nbsp;<?php echo esc_html( fs_currency() ) ?></td>
            </tr>
            </tfoot>

        </table>
    </div>
</div>