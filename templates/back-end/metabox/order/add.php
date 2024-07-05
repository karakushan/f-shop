<div class="app">
    <input type="hidden" name="fs_is_admin" value="1">
    <input type="hidden" name="fs_create_form" value="1">

    <vue-order-items
            :items='<?php echo json_encode( isset( $order->items ) ? $order->items : [] ) ?>'></vue-order-items>

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
                           value="">
                    <p class="field-description"><?php esc_html_e( 'If you do not specify the customer ID, the customer will be created automatically', 'f-shop' ); ?></p>
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
                <td><input type="text" name="user[last_name]">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Phone number', 'f-shop' ) ?><sup>*</sup></th>
                <td><input type="number" name="user[phone]" required>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'E-mail', 'f-shop' ) ?></th>
                <td><input type="email" name="user[email]">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'City', 'f-shop' ) ?></th>
                <td><input type="text" name="user[city]">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Delivery method', 'f-shop' ) ?></th>
                <td>
                    <select name="order[_delivery][method]" class="fs-select-field">
						<?php foreach ( $shipping_methods as $shipping_method ): ?>
                            <option value="<?php echo esc_attr( $shipping_method->term_id ); ?>"><?php echo apply_filters( 'the_title', $shipping_method->name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Delivery service branch number', 'f-shop' ) ?>:</th>
                <td>
                    <input type="text" name="order[_delivery][secession]">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Delivery address', 'f-shop' ) ?></th>
                <td><input type="text" name="user[address]"></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Payment method', 'f-shop' ) ?></th>
                <td>
                    <select name="order[_payment]" class="fs-select-field">
						<?php foreach ( $payment_methods as $payment_method ): ?>
                            <option value="<?php echo esc_attr( $payment_method->term_id ); ?>"><?php echo apply_filters( 'the_title', $payment_method->name ) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Comment to the order', 'f-shop' ) ?></th>
                <td>
                    <textarea name="order[_comment]" rows="10"></textarea>
                </td>
            </tr>
            </tbody>
        </table>
    </section>
</div>



