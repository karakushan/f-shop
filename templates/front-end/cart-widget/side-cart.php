<div class="side-cart animated slideInRight">
    <div class="side-cart__title"><?php esc_html_e( 'Basket', 'f-shop' ) ?>(<?php fs_product_count() ?>)
        <a href="<?php echo esc_url( fs_get_catalog_link() ) ?>"
           class="close-cart">&times;</a>
    </div>
	<?php $cart_items = fs_get_cart(); ?>
    <ul class="side-cart__items">
		<?php foreach ( $cart_items as $num => $cart_item ): ?>
			<?php $item = fs_set_product( $cart_item, $num ) ?>
            <li>
				<?php fs_delete_position( $num, [
					'content' => '<i class="icon icon-close"></i>',
					'refresh' => false
				] ) ?>
				<?php echo $cart_item['thumbnail'] ?>
                <div class="side-cart__items-meta">
                    <a href="<?php echo esc_url( $cart_item['link'] ) ?>"
                       class="title" target="_blank"><?php echo $item->get_title() ?></a>
					<?php $item->the_sku() ?>
                    <div class="prices">
                        <div class="fs-price"><?php if ( $item->count > 1 ) {
								echo $item->count . ' &times; ';
							} ?><?php $item->the_price() ?></div>
                    </div>


                </div>
            </li>
		<?php endforeach; ?>
    </ul>

    <div class="side-cart__bottom">
        <div class="total"><span>Итого:</span><?php fs_total_amount() ?></div>
        <div class="bts-group">
            <a href="<?php echo esc_url( fs_get_checkout_page_link() ) ?>"
               class="btn btn-primary btn-lg btn-block"><?php esc_html_e( 'Checkout', 'f-shop' ) ?></a>
            <a href="<?php echo esc_url( fs_get_catalog_link() ) ?>"
               class="btn btn-danger btn-lg btn-block close-cart"><?php esc_html_e( 'To catalog', 'f-shop' ) ?></a>
        </div>
    </div>

</div>
