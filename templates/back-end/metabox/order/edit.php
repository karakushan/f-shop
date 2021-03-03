<?php do_action( 'qm/debug', $order );  ?>
<input type="hidden" name="fs_is_admin" value="1">
<div class="app">
    <vue-order-items
            :items='<?php echo json_encode( isset( $order->items ) ? $order->items : [] ) ?>'
            :order='<?php echo json_encode( $order ) ?>'
    >
        <template v-slot:tfooter>
            <md-toolbar md-elevation="1">
                <div class="md-toolbar-row md-body-2">
                    <div style="flex: 1"><?php esc_html_e( 'Packing', 'f-shop' ); ?>:</div>
                    <span><?php echo esc_html( apply_filters( 'fs_price_format', $order->packing_cost ) . ' ' . fs_currency() ); ?></span>
                </div>
                <div class="md-toolbar-row md-body-2">
                    <div style="flex: 1"><?php esc_html_e( 'Delivery', 'f-shop' ); ?>:</div>
                    <span><?php echo esc_html( apply_filters( 'fs_price_format', $order->shipping_cost ) . ' ' . fs_currency() ); ?></span>
                </div>
                <div class="md-toolbar-row md-body-2">
                    <div style="flex: 1"><?php esc_html_e( 'Discount', 'f-shop' ); ?>:</div>
                    <span><?php echo esc_html( apply_filters( 'fs_price_format', $order->discount ) . ' ' . fs_currency() ); ?></span>
                </div>
                <div class="md-toolbar-row md-title">
                    <div style="flex: 1"><?php esc_html_e( 'Total', 'f-shop' ); ?>:</div>
                    <div><?php echo esc_html( apply_filters( 'fs_price_format', $order->total_amount ) . ' ' . fs_currency() ); ?></div>
                </div>

            </md-toolbar>
        </template>
    </vue-order-items>

    <!--Buyer details-->
    <section class="section">
        <md-toolbar :md-elevation="1">
            <span class="md-title">
                <md-icon>person</md-icon> <?php esc_html_e( 'Buyer details', 'f-shop' ); ?></span>
        </md-toolbar>
        <table class="wp-list-table widefat fixed striped order-userdata">
            <tbody>
            <tr>
                <th><?php esc_html_e( 'ID', 'f-shop' ) ?></th>
                <td>
                    <input type="number" min="1" name="user[customer_ID]"
                           value="<?php echo esc_attr( $order->customer_ID ) ?>" readonly>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'First name', 'f-shop' ) ?><sup>*</sup></th>
                <td><input type="text" name="user[first_name]"
                           value="<?php echo esc_attr( $order->customer->first_name ); ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Last name', 'f-shop' ) ?></th>
                <td><input type="text" name="user[last_name]"
                           value="<?php if ( isset( $order->customer->last_name ) ) {
					           echo esc_attr( $order->customer->last_name );
				           } ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Phone number', 'f-shop' ) ?><sup>*</sup></th>
                <td><input type="number" name="user[phone]"
                           value="<?php if ( isset( $order->customer->phone ) ) {
					           echo esc_attr( $order->customer->phone );
				           } ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'E-mail', 'f-shop' ) ?></th>
                <td><input type="email"
                           name="user[email]"
                           value="<?php if ( isset( $order->customer->email ) ) {
					           echo esc_attr( $order->customer->email );
				           } ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'City', 'f-shop' ) ?></th>
                <td><input type="text" name="user[city]"
                           value="<?php if ( isset( $order->customer->city ) ) {
					           echo esc_attr( $order->customer->city );
				           } ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Delivery method', 'f-shop' ) ?></th>
                <td>
                    <select name="order[_delivery][method]" class="fs-select-field">
						<?php foreach ( $shipping_methods as $shipping_method ): ?>
                            <option value="<?php echo esc_attr( $shipping_method->term_id ); ?>" <?php selected( $order->delivery_method->term_id, $shipping_method->term_id ) ?>><?php echo apply_filters( 'the_title', $shipping_method->name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Delivery service branch number', 'f-shop' ) ?>:</th>
                <td>
                    <input type="text" name="order[_delivery][secession]"
                           value="<?php echo esc_attr( $order->delivery_method->delivery_service_number ); ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Delivery address', 'f-shop' ) ?></th>
                <td><input type="text" name="user[address]"
                           value="<?php if ( isset( $order->customer->address ) ) {
					           echo esc_attr( $order->customer->address );
				           } ?>"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Payment method', 'f-shop' ) ?></th>
                <td>
                    <select name="order[_payment]" class="fs-select-field">
						<?php foreach ( $payment_methods as $payment_method ): ?>
                            <option value="<?php echo esc_attr( $payment_method->term_id ); ?>" <?php selected( $order->payment_method->term_id, $payment_method->term_id ) ?>><?php echo apply_filters( 'the_title', $payment_method->name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Comment to the order', 'f-shop' ) ?></th>
                <td>
            <textarea name="order[_comment]" rows="10">
                <?php echo $order->comment; ?>
            </textarea>
                </td>
            </tr>
            </tbody>
        </table>
    </section>

    <!--История заказа -->
    <section class="section fs-order-history">
        <md-toolbar :md-elevation="1">
            <span class="md-title"><md-icon>insights</md-icon> <?php esc_html_e( 'Order history', 'f-shop' ); ?></span>
        </md-toolbar>
        <md-list>

			<?php foreach ( $order->get_order_history() as $event ): ?>
                <md-list-item md-expand>
                    <div class="md-list-item-text">
                        <span class="fs-order-history__date"><?php echo date_i18n( 'd F Y H:i', $event['time'] ) ?></span>
                        <span class="fs-order-history__event"><?php echo esc_html( $event['name'] ); ?></span>
                    </div>

                    <md-list slot="md-expand">
                        <md-list-item class="md-inset">
							<?php echo $event['description']; ?>
                        </md-list-item>
                    </md-list>
                </md-list-item>
                <md-divider></md-divider>
			<?php endforeach; ?>


        </md-list>

    </section>
</div>




