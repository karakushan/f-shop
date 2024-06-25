<div class="fs-cart-listing">
    <h3 class="checkout-title"><?php esc_html_e( 'Items in the cart', 'f-shop' ) ?></h3>
    <div class="table-responsive">
        <table class="table table-condensed">
            <thead class="thead-light">
            <tr>
                <td>
					<?php esc_html_e( 'Photo', 'f-shop' ); ?>
                </td>
                <td>
					<?php esc_html_e( 'Product', 'f-shop' ); ?>
                </td>
                <td>
					<?php esc_html_e( 'Vendor code', 'f-shop' ); ?>
                </td>
                <td>
					<?php esc_html_e( 'Price', 'f-shop' ); ?>
                </td>
                <td>
					<?php esc_html_e( 'Quantity', 'f-shop' ); ?>
                </td>
                <td>
					<?php esc_html_e( 'Cost', 'f-shop' ); ?>
                </td>
                <td></td>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach ( $cart as $item_id => $product ): ?>
				<?php
				$item = fs_set_product( $product, $item_id ) ?>
                <tr>
                    <td>
						<?php fs_product_thumbnail( $item->id ) ?>
                    </td>
                    <td>
                        <div class="info">
                            <a href="<?php echo esc_url( $item->permalink ) ?>" target="_blank"
                               class="name"><?php echo esc_html( $item->title ) ?></a>
                        </div>
                    </td>
                    <td>
						<?php $item->the_sku() ?>
                    </td>
                    <td style="white-space: nowrap;">
						<?php $item->the_price() ?>
                    </td>
                    <td>
						<?php $item->cart_quantity( array(
							'pluss' => array(
								'class'   => 'fs-pluss',
								'content' => '<span class="glyphicon glyphicon-plus"></span>'
							),
							'minus' => array(
								'class'   => 'fs-minus',
								'content' => '<span class="glyphicon glyphicon-minus"></span>'
							),
						) ) ?>
                    </td>
                    <td style="white-space: nowrap;">
						<?php $item->the_cost() ?>
                    </td>
                    <td>
						<?php $item->delete_position( array(
							'class'   => 'btn btn-outline-danger btn-sm',
							'type'    => 'button',
							'content' => '&times;'
						) ) ?>
                    </td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="fs-checkout-sumary">
        <table class="table">
            <tr>
                <td>Стоимость товаров:</td>
                <td><?php fs_cart_cost() ?></td>
            </tr>
            <tr>
                <td>Стоимость доставки:</td>
                <td><?php fs_delivery_cost() ?></td>
            </tr>
            <tr>
                <td>Скидка:</td>
                <td><?php fs_total_discount() ?></td>
            </tr>
            <tr>
                <td>Всего к оплате:</td>
                <td><?php fs_total_amount() ?></td>
            </tr>
        </table>
    </div>
    <h3 class="checkout-title"><?php esc_html_e( 'Information about delivery', 'f-shop' ) ?></h3>
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
						<?php fs_form_field( 'fs_first_name' ) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
						<?php fs_form_field( 'fs_last_name' ) ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
				<?php fs_form_field( 'fs_city' ) ?>
            </div>
            <div class="form-group">
				<?php fs_form_field( 'fs_email' ) ?>
            </div>
            <div class="form-group">
				<?php fs_form_field( 'fs_phone' ) ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
						<?php fs_form_field( 'fs_payment_methods' ) ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
						<?php fs_form_field( 'fs_delivery_methods' ) ?>
						<?php do_action( 'fs_shipping_fields' ) ?>
                    </div>
                </div>
            </div>
            <div class="form-group"><?php fs_form_field( 'fs_comment' ) ?></div>
        </div>
    </div>
	<?php if ( ! is_user_logged_in() ): ?>
        <div class="form-group">
			<?php fs_form_field( 'fs_customer_register' ) ?>
        </div>
	<?php endif ?>
    <p class="text-center">
		<?php fs_order_send(); ?>
    </p>
</div>