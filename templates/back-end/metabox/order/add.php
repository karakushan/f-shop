<input type="hidden" name="fs_is_admin" value="1">

<!--Purchased items-->
<section class="section">
    <h2 class="order-title"><?php esc_html_e( 'Purchased items', 'f-shop' ); ?>
        <a href="#TB_inline?width=600&height=600&inlineId=fs-search-product-modal"
           class="button thickbox"
           title="<?php esc_attr_e( 'Поиск товара', 'f-shop' ); ?>"><?php esc_html_e( 'Добавить товар', 'f-shop' ); ?></a>
    </h2>


    <table class="wp-list-table widefat fixed striped order-items">
        <thead>
        <tr>
            <th><?php esc_html_e( 'ID', 'f-shop' ) ?></th>
            <th><?php esc_html_e( 'Photo', 'f-shop' ) ?></th>
            <th><?php esc_html_e( 'Title', 'f-shop' ) ?></th>
            <th><?php esc_html_e( 'SKU', 'f-shop' ) ?></th>
            <th><?php esc_html_e( 'Price', 'f-shop' ) ?></th>
            <th><?php esc_html_e( 'Quantity', 'f-shop' ) ?></th>
            <th><?php esc_html_e( 'Attributes', 'f-shop' ) ?></th>
            <th><?php esc_html_e( 'Cost', 'f-shop' ) ?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
		<?php if ( count( $order->items ) ): ?>
			<?php foreach ( $order->items as $variation_id => $product ): ?>
				<?php
				$offer = fs_set_product( $product );
				?>
                <tr>
                    <td>
						<?php echo esc_html( $offer->id ) ?>
                        <input type="hidden" name="fs_products[<?php echo $variation_id ?>][ID]"
                               value="<?php echo esc_attr( $offer->id ) ?>">
                    </td>
                    <td class="order-items__image">
                        <a href="<?php echo esc_url( $offer->permalink ) ?>" target="_blank"
                           title="перейти к товару">
							<?php fs_product_thumbnail( $offer->id, 'thumbnail' ) ?>
                        </a>
                    </td>
                    <td><a href="<?php echo esc_url( $offer->permalink ) ?>" target="_blank"
                           title="перейти к товару"><strong><?php echo esc_attr( $offer->title ) ?></strong></a></td>
                    <td><?php echo esc_attr( $offer->sku ) ?></td>
                    <td><?php echo esc_attr( $offer->price_display ) ?>
                        &nbsp;<?php echo esc_attr( $offer->currency ) ?></td>
                    <td>
                        <input type="number" min="1" step="1" size="3"
                               name="fs_products[<?php echo $variation_id ?>][count]"
                               value="<?php echo esc_attr( $offer->count ) ?>">
                    </td>
                    <td>
						<?php
						if ( count( $offer->attributes ) ) {
							echo '<ul class="product-att">';
							foreach ( $offer->attributes as $att ) {
								echo '<li><b>' . esc_attr( apply_filters( 'the_title', $att->parent_name ) ) . '</b>: ' . esc_attr( apply_filters( 'the_title', $att->name ) ) . '</li>';
							}
							echo '</ul>';
						}
						?>
                    </td>
                    <td><?php echo esc_attr( $offer->cost_display ) ?>
                        &nbsp;<?php echo esc_attr( $offer->currency ) ?></td>
                    <td>
                        <button type="button" class="remove-from-cart"
                                title="<?php echo esc_attr_e( 'Remove from order', 'f-shop' ); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </td>
                </tr>
			<?php endforeach; ?>
		<?php endif ?>
        </tbody>
        <tfoot>

        <tr>
            <td colspan="9">
                <h4 style="margin: 0;">
					<?php printf( __( 'Total cost', 'f-shop' ) . ': %s <span>%s</span>', apply_filters( 'fs_price_format', $order->total_amount ), fs_currency() ) ?>
                </h4>
            </td>
        </tr>
        </tfoot>
    </table>


</section>
<!--Buyer details-->
<section class="section">
    <h2 class="order-title"><?php esc_html_e( 'Buyer details', 'f-shop' ); ?></h2>
    <table class="wp-list-table widefat fixed striped order-userdata">
        <tbody>
        <tr>
            <th><?php esc_html_e( 'ID', 'f-shop' ) ?></th>
            <td>
                <select name="user[fs_user_id]" class="fs-select-field">
                    <option value="0"><?php esc_attr_e( 'Choose from buyers', 'f-shop'  ) ?></option>
					<?php foreach ( $clients as $client ): ?>
                        <option value="<?php echo esc_attr( $client->ID ); ?>"><?php echo apply_filters( 'the_title', '[' . $client->ID . '] ' . get_user_meta( $client->ID, 'first_name', true ) . ' ' . get_user_meta( $client->ID, 'last_name', true ) ) ?></option>
					<?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'First name', 'f-shop' ) ?></th>
            <td><input type="text" name="user[fs_first_name]"></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Last name', 'f-shop' ) ?></th>
            <td><input type="text" name="user[fs_last_name]"></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Phone number', 'f-shop' ) ?></th>
            <td><input type="text" name="user[fs_phone]"></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'E-mail', 'f-shop' ) ?></th>
            <td><input type="email" name="user[fs_email]"></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'City', 'f-shop' ) ?></th>
            <td><input type="text" name="user[fs_city]"></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Delivery method', 'f-shop' ) ?></th>
            <td>
                <select name="user[fs_delivery_methods]" class="fs-select-field">
					<?php foreach ( $shipping_methods as $shipping_method ): ?>
                        <option value="<?php echo esc_attr( $shipping_method->term_id ); ?>"><?php echo apply_filters( 'the_title', $shipping_method->name ) ?></option>
					<?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Delivery service address', 'f-shop' ) ?>:</th>
            <td><input type="text" name="user[fs_address]"></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Delivery address', 'f-shop' ) ?></th>
            <td><input type="text" name="user[fs_address]"></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Payment method', 'f-shop' ) ?></th>
            <td>
                <select name="user[fs_delivery_methods]" class="fs-select-field">
					<?php foreach ( $payment_methods as $payment_method ): ?>
                        <option value="<?php echo esc_attr( $payment_method->term_id ); ?>"><?php echo apply_filters( 'the_title', $payment_method->name ) ?></option>
					<?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Comment to the order', 'f-shop' ) ?></th>
            <td>
                <textarea name="user[fs_comment]" rows="10"></textarea>
            </td>
        </tr>
        </tbody>
    </table>
</section>

<!-- Modal product selection window -->
<div id="fs-search-product-modal" style="display:none;">
    <div class="fs-search-product" id="fs-search-product">
        <input type="text" class="search-input" id="fs-search-input"
               placeholder="<?php esc_attr_e( 'Enter product name or ID', 'f-shop' ); ?>">
        <div class="selected">
            <ul></ul>
            <button type="button"
                    class="button button-primary assign-order"><?php esc_html_e( 'Add', 'f-shop' ); ?></button>
        </div>
        <div class="results" id="fs-search-product-results">

        </div>
    </div>
</div>

