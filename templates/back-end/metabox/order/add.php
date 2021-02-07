<div class="app">
    <input type="hidden" name="fs_is_admin" value="1">

    <vue-order-items :items='<?php  echo json_encode(isset($order->items) ? $order->items : []) ?>'></vue-order-items>

    <!--Buyer details-->
    <section class="section">
        <md-toolbar :md-elevation="1">
            <span class="md-title"><?php esc_html_e( 'Buyer details', 'f-shop' ); ?></span>
        </md-toolbar>
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
</div>



