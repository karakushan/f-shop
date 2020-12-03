<?php if ( $orders ): ?>
    <div class="fs-dashboard-orders">
		<?php foreach ( $orders as $post ): ?>
			<?php $order = fs_set_order( $post ) ?>
            <div <?php post_class( 'fs-dashboard-order' ) ?>>
                <div class="fs-dashboard-order__header">
                    <span class="badge badge-primary"> <i><?php echo $order->status ?></i></span>
                    <span class="datetime"><?php the_time( 'd.m.Y H:i' ) ?></span>
                    <span><?php esc_html_e( 'Items', 'f-shop' ); ?>: <i><?php echo $order->count ?></i></span>
                    <span><?php esc_html_e( 'Total cost', 'f-shop' ); ?>: <i><?php echo apply_filters( 'fs_price_format', $order ) ?>&nbsp;<?php echo fs_currency() ?></i></span>
                    <button type="button"
                            class="btn btn-primary btn-sm" data-toggle="collapse"
                            data-target="#fs-dashboard-order-<?php $order->ID ?>"><?php esc_html_e( 'Order details', 'f-shop' ); ?></button>
                </div>
                <div class="fs-dashboard-order__hide collapse" id="fs-dashboard-order-<?php $order->ID ?>">
                    <div class="row">
                        <div class="col-lg-12">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col" style="width: 270px;"><?php esc_html_e( 'Title', 'f-shop' ); ?></th>
                                    <th scope="col" style="width: 224px;"><?php esc_html_e( 'Photo', 'f-shop' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Price', 'f-shop' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Qty', 'f-shop' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Cost', 'f-shop' ); ?></th>
                                </tr>
                                </thead>
                                <tbody>
								<?php
								foreach ( $order->items as $key => $product ): ?>
									<?php  $product = fs_set_product( $product, $key ); ?>
                                    <tr>
                                        <td><a href="<?php the_permalink( $product['ID'] ) ?>"
                                               target="_blank"><?php $product->the_title(); ?></a>
                                        </td>
                                        <td><?php $product->the_thumbnail(); ?></td>
                                        <td><?php $product->the_price(); ?></td>
                                        <td><?php echo esc_html( $product->count ) ?></td>
                                        <td><?php echo esc_html($product->cost_display) ?></td>
                                    </tr>
								<?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="label"><?php esc_html_e( 'Delivery', 'f-shop' ) ?></div>
                        </div>
                        <div class="col-lg-9">
                            <p><?php echo esc_html(  $order->delivery_method ) ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="label"><?php esc_html_e( 'Payment', 'f-shop' ) ?></div>
                        </div>
                        <div class="col-lg-9">
                            <p><?php echo esc_html(  $order->payment_method ) ?></p>
                        </div>
                    </div>
                </div>
            </div>
		<?php endforeach; ?>
    </div>
<?php else: ?>
    <p><?php esc_html_e( 'You currently have no orders.', 'f-shop' ) ?></p>
<?php endif ?>
