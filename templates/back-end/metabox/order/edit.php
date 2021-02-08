<input type="hidden" name="fs_is_admin" value="1">
<div class="app">
    <vue-order-items
            :items='<?php echo json_encode( isset( $order->items ) ? $order->items : [] ) ?>'></vue-order-items>

    <!--Buyer details-->
    <section class="section">
        <md-toolbar :md-elevation="1">
            <span class="md-title"><md-icon>person</md-icon> <?php esc_html_e( 'Buyer details', 'f-shop' ); ?></span>
        </md-toolbar>
        <table class="wp-list-table widefat fixed striped order-userdata">
            <tbody>
            <tr>
                <th><?php esc_html_e( 'ID', 'f-shop' ) ?></th>
                <td>
                    <select name="user[fs_user_id]" class="fs-select-field">
                        <option value="0"><?php esc_attr_e( 'Choose from buyers', 'f-shop' ) ?></option>

						<?php foreach ( $clients as $client ): ?>
                            <option value="<?php echo esc_attr( $client->ID ); ?>" <?php selected( $order->user['id'], $client->ID ) ?>><?php echo apply_filters( 'the_title', '[' . $client->ID . '] ' . get_user_meta( $client->ID, 'first_name', true ) . ' ' . get_user_meta( $client->ID, 'last_name', true ) ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'First name', 'f-shop' ) ?></th>
                <td><input type="text" name="user[fs_first_name]"
                           value="<?php echo esc_attr( $order->user['first_name'] ); ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Last name', 'f-shop' ) ?></th>
                <td><input type="text" name="user[fs_last_name]"
                           value="<?php echo esc_attr( $order->user['last_name'] ); ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Phone number', 'f-shop' ) ?></th>
                <td><input type="text" name="user[fs_phone]" value="<?php echo esc_attr( $order->user['phone'] ); ?>"
                           required>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'E-mail', 'f-shop' ) ?></th>
                <td><input type="email" name="user[fs_email]" value="<?php echo esc_attr( $order->user['email'] ); ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'City', 'f-shop' ) ?></th>
                <td><input type="text" name="user[fs_city]"
                           value="<?php echo esc_attr( $order->delivery_method->city ); ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Delivery method', 'f-shop' ) ?></th>
                <td>
                    <select name="user[fs_delivery_methods]" class="fs-select-field">
						<?php foreach ( $shipping_methods as $shipping_method ): ?>
                            <option value="<?php echo esc_attr( $shipping_method->term_id ); ?>" <?php selected( $order->delivery_method->term_id, $shipping_method->term_id ) ?>><?php echo apply_filters( 'the_title', $shipping_method->name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Номер отделения службы доставки', 'f-shop' ) ?>:</th>
                <td>
                    <input type="number" name="user[fs_delivery_number]" min="0" step="1"
                           value="<?php echo esc_attr( $order->delivery_method->delivery_service_number ); ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Delivery address', 'f-shop' ) ?></th>
                <td><input type="text" name="user[fs_address]"
                           value="<?php echo esc_attr( $order->delivery_method->delivery_address ); ?>"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Payment method', 'f-shop' ) ?></th>
                <td>
                    <select name="user[fs_payment_methods]" class="fs-select-field">
						<?php foreach ( $payment_methods as $payment_method ): ?>
                            <option value="<?php echo esc_attr( $payment_method->term_id ); ?>" <?php selected( $order->payment_method->term_id, $payment_method->term_id ) ?>><?php echo apply_filters( 'the_title', $payment_method->name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Comment to the order', 'f-shop' ) ?></th>
                <td>
            <textarea name="user[fs_comment]" rows="10">
                <?php echo $order->comment; ?>
            </textarea>
                </td>
            </tr>
            </tbody>
        </table>
    </section>

    <!--История заказа -->
	<?php // do_action( 'qm/debug', $order ); ?>
	<?php do_action( 'qm/debug', $order->get_order_history() ); ?>
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




