<table class="wp-list-table widefat fixed striped order-user">
    <tbody>
    <tr>
        <th><?php esc_html_e('ID', 'f-shop') ?></th>
        <td><?php echo esc_html($order->user['id']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('IP', 'f-shop') ?></th>
        <td><?php echo esc_html($order->user['ip']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('First name', 'f-shop') ?></th>
        <td><?php echo esc_html($order->user['first_name']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Last name', 'f-shop') ?></th>
        <td><?php echo esc_html($order->user['last_name']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Phone number', 'f-shop') ?></th>
        <td><?php echo esc_html($order->user['phone']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('E-mail', 'f-shop') ?></th>
        <td><?php echo esc_html($order->user['email']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('City', 'f-shop') ?></th>
        <td><?php echo esc_html($order->delivery_method->city) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Delivery method', 'f-shop') ?></th>
        <td><?php echo esc_html($order->delivery_method->name) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Delivery service address', 'f-shop') ?>:</th>
        <td><?php echo esc_html($delivery['secession']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Delivery address', 'f-shop') ?></th>
        <td><?php echo esc_html($order->delivery_method->delivery_address) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Payment method', 'f-shop') ?></th>
        <td><?php echo esc_html($order->payment_method->name) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Comment to the order', 'f-shop') ?></th>
        <td><?php echo esc_html($order->comment) ?></td>
    </tr>
    </tbody>
</table>
