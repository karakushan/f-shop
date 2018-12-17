<h3><?php esc_html_e( 'My orders', 'f-shop' ) ?></h3>
<?php if ( ! empty( $orders ) ) { ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Order number', 'f-shop' ) ?></th>
                <th><?php esc_html_e( 'Date', 'f-shop' ) ?></th>
                <th><?php esc_html_e( 'Quantity', 'f-shop' ) ?></th>
                <th><?php esc_html_e( 'Status', 'f-shop' ) ?></th>
                <th><?php esc_html_e( 'Total Cost', 'f-shop' ) ?></th>
                <th>-</th>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach ( $orders as $order ) {
				?>
                <tr>
                    <td><?php echo esc_html( $order->ID ) ?></td>
                    <td><?php echo esc_html( date( 'd.m.Y H:i', strtotime( $order->post_date ) ) ) ?></td>
                    <td></td>
                    <td><?php echo esc_html( $order->post_status ) ?></td>
                    <td><?php echo esc_html( $order->data->_amount ) ?>
                        &nbsp;<?php echo esc_html( fs_currency() ) ?></td>
                    <td>
                        <a href="<?php echo esc_url( add_query_arg( array( 'order_detail' => $order->ID ) ) ) ?>"
                           class="btn btn-secondary btn-sm"><?php esc_html_e( 'read more', 'f-shop' ) ?></a>
                    </td>
                </tr>

			<?php } ?>
            </tbody>
        </table>
    </div>
<?php } else { ?>
    <p><?php esc_html_e( 'You currently have no orders', 'f-shop' ); ?>.</p>
<?php } ?>