<?php
/**
 * @var \FS\FS_Order $order
 * @var \FS\FS_Product $item
 */
?>
<input type="hidden" name="fs_is_admin" value="1">
<div class="fs-order-edit" x-data="{
    showSelectProduct:false,
    items: <?php echo htmlspecialchars( json_encode( $order->getItems() ) ) ?>
    }"
     x-on:attach-products="($event)=>{
         if ($event.detail.items.length>0){
             $event.detail.items.forEach((item)=>{
                 items.push(item);
             })
         }
     }">

	<?php do_action( 'fs_select_product' ); ?>

    <!--Products-->
    <section class="fs-order-edit__section">
        <div class="fs-order-edit__toolbar">
            <h3 class="fs-order-edit__title"><?php _e( 'Products', 'f-shop' ); ?></h3>
            <button type="button" class="button"
                    x-on:click.prevent="showSelectProduct=true"><?php _e( 'Add product', 'f-shop' ); ?></button>
        </div>

        <div class="fs-order-edit__items">
            <div class="fs-order-edit__item fs-order-edit__item--header" x-show="items.length>0">
                <div class="fs-order-edit__item-id"><?php _e( 'ID', 'f-shop' ); ?></div>
                <div class="fs-order-edit__item-photo"><?php _e( 'Photo', 'f-shop' ); ?></div>
                <div class="fs-order-edit__item-name"><?php _e( 'Name', 'f-shop' ); ?></div>
                <div class="fs-order-edit__item-price"><?php _e( 'Price', 'f-shop' ); ?></div>
                <div class="fs-order-edit__item-qty"><?php _e( 'Quantity', 'f-shop' ); ?></div>
                <div class="fs-order-edit__item-cost"><?php _e( 'Cost', 'f-shop' ); ?></div>
                <div class="fs-order-edit__item-action"><?php _e( 'Action', 'f-shop' ); ?></div>
            </div>

            <div x-show="items.length===0" class="fs-order-edit__no-items">
				<?php _e( 'В заказе нет товаров', 'f-shop' ); ?>
            </div>

            <template x-for="(item, index) in items">
                <div class="fs-order-edit__item">
                    <div class="fs-order-edit__item-id" x-text="item.id"></div>
                    <div class="fs-order-edit__item-photo">
                        <img :src="item.thumbnail_url" alt="" width="100">
                    </div>
                    <div class="fs-order-edit__item-name">
                        <a :href="item.permalink" target="_blank"
                           x-text="item.title"
                           title="<?php _e( 'Open in new tab', 'f-shop' ); ?>"
                        ></a>
                    </div>
                    <div class="fs-order-edit__item-price">
                        <span x-text="item.price"></span>
                        <span x-text="item.currency" class="fs-order-edit__item-currency"></span>
                    </div>
                    <div class="fs-order-edit__item-qty">
                        <input type="number" size="2" min="1" step="1" x-model.number="item.count">
                    </div>
                    <div class="fs-order-edit__item-cost">
                        <span x-text="item.price*item.count"></span>
                        <span x-text="item.currency" class="fs-order-edit__item-currency"></span>
                    </div>
                    <div class="fs-order-edit__item-action">
                        <button x-on:click.prevent="if(confirm('<?php _e( 'Do you confirm deletion?', 'f-shop' ); ?>')) items.splice(index, 1)"
                                class="fs-order-edit__btn-danger"
                                title="<?php _e( 'Delete', 'f-shop' ); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
            </template>

        </div>
    </section>

    <!--Buyer details-->
    <section class="fs-order-edit__section">
        <div class="fs-order-edit__toolbar">
            <h3 class="fs-order-edit__title">
				<?php _e( 'Buyer', 'f-shop' ); ?>
            </h3>
        </div>
        <table class="wp-list-table widefat fixed striped order-userdata">
            <tbody>
            <tr>
                <th><?php _e( 'ID', 'f-shop' ) ?></th>
                <td>
                    <input type="number" min="1" name="user[customer_ID]"
                           value="<?php echo esc_attr( $order->customer_ID ) ?>" readonly>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Type', 'f-shop' ) ?></th>
                <td>
                    <select name="order[_order_type]" id="order_type">
                        <option value="standard" <?php selected( $order->order_type, 'standard' ) ?>>
							<?php _e( 'Standard order', 'f-shop' ) ?>
                        </option>
                        <option value="quick" <?php selected( $order->order_type, 'quick' ) ?>>
							<?php _e( 'Quick order', 'f-shop' ) ?>
                        </option>
                    </select>

                </td>
            </tr>
            <tr>
                <th><?php _e( 'First name', 'f-shop' ) ?><sup>*</sup></th>
                <td><input type="text" name="user[first_name]"
                           value="<?php echo esc_attr( $order->customer->first_name ); ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Last name', 'f-shop' ) ?></th>
                <td>
                    <input type="text" name="order[_order_type]"
                           value="<?php if ( isset( $order->customer->last_name ) ) {
						       echo esc_attr( $order->customer->last_name );
					       } ?>">
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Phone number', 'f-shop' ) ?><sup>*</sup></th>
                <td><input type="number" name="user[phone]"
                           value="<?php if ( isset( $order->customer->phone ) ) {
					           echo esc_attr( $order->customer->phone );
				           } ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'E-mail', 'f-shop' ) ?></th>
                <td><input type="email"
                           name="user[email]"
                           value="<?php if ( isset( $order->customer->email ) ) {
					           echo esc_attr( $order->customer->email );
				           } ?>">
                </td>
            </tr>
            <tr>
                <th><?php _e( 'City', 'f-shop' ) ?></th>
                <td><input type="text" name="user[city]"
                           value="<?php if ( isset( $order->customer->city ) ) {
					           echo esc_attr( $order->customer->city );
				           } ?>">
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Delivery method', 'f-shop' ) ?></th>
                <td>
                    <select name="order[_delivery][method]" class="fs-select-field">
						<?php foreach ( $shipping_methods as $shipping_method ): ?>
                            <option value="<?php echo esc_attr( $shipping_method->term_id ); ?>" <?php selected( $order->delivery_method->term_id, $shipping_method->term_id ) ?>><?php echo apply_filters( 'the_title', $shipping_method->name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Delivery service branch number', 'f-shop' ) ?>:</th>
                <td>
                    <input type="text" name="order[_delivery][secession]"
                           value="<?php echo esc_attr( $order->delivery_method->delivery_service_number ); ?>">
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Delivery address', 'f-shop' ) ?></th>
                <td><input type="text" name="user[address]"
                           value="<?php if ( isset( $order->customer->address ) ) {
					           echo esc_attr( $order->customer->address );
				           } ?>"></td>
            </tr>
            <tr>
                <th><?php _e( 'Payment method', 'f-shop' ) ?></th>
                <td>
                    <select name="order[_payment]" class="fs-select-field">
						<?php foreach ( $payment_methods as $payment_method ): ?>
                            <option value="<?php echo esc_attr( $payment_method->term_id ); ?>" <?php selected( $order->payment_method->term_id, $payment_method->term_id ) ?>><?php echo apply_filters( 'the_title', $payment_method->name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Comment to the order', 'f-shop' ) ?></th>
                <td>
            <textarea name="order[_comment]" rows="10">
                <?php echo $order->comment; ?>
            </textarea>
                </td>
            </tr>
            </tbody>
        </table>
    </section>

    <!--Order history -->
    <section class="fs-order-edit__section">
        <div class="fs-order-edit__toolbar">
            <h3 class="fs-order-edit__title">
				<?php _e( 'Order history', 'f-shop' ); ?>
            </h3>
        </div>
        <div class="fs-order-history">
			<?php foreach ( $order->get_order_history() as $event ): ?>
                <div x-data="{isOpen:false}" class="fs-order-history__item"
                     :class="{'fs-order-history__item-open': isOpen}">
                    <div class="fs-order-history__header" x-on:click.prevent="isOpen = !isOpen">
                        <div class="fs-order-history__title">
                            <span class="fs-order-history__date"><?php echo date_i18n( 'd F Y H:i', $event['time'] ) ?></span>
                            <span class="fs-order-history__event"><?php echo esc_html( $event['name'] ); ?></span>
                        </div>
                        <button class="fs-order-history__toggle">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                    <div x-show="isOpen" style="display: none;" class="fs-order-history__body">
						<?php echo $event['description']; ?>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>

    </section>
</div>





