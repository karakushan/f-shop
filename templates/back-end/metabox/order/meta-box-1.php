<table class="wp-list-table widefat fixed striped order-user">
    <tbody>
    <tr>
        <th><?php esc_html_e('ID', 'f-shop') ?></th>
        <td><?php echo esc_html($user['id']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('First name', 'f-shop') ?></th>
        <td><?php echo esc_html($user['first_name']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Last name', 'f-shop') ?></th>
        <td><?php echo esc_html($user['last_name']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Phone number', 'f-shop') ?></th>
        <td><?php echo esc_html($user['phone']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('E-mail', 'f-shop') ?></th>
        <td><?php echo esc_html($user['email']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('City', 'f-shop') ?></th>
        <td><?php echo esc_html($user['city']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Delivery method', 'f-shop') ?></th>
        <td><?php echo esc_html(apply_filters('the_title', get_term_field('name', $delivery['method'], 'fs-delivery-methods'))) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Delivery service address', 'f-shop') ?>:</th>
        <td><?php echo esc_html($delivery['secession']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Delivery address', 'f-shop') ?></th>
        <td><?php echo esc_html($delivery['adress']) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Payment method', 'f-shop') ?></th>
        <td><?php echo esc_html(apply_filters('the_title', get_term_field('name', $payment, 'fs-payment-methods'))) ?></td>
    </tr>
    <tr>
        <th><?php esc_html_e('Comment to the order', 'f-shop') ?></th>
        <td><?php echo esc_html(get_post_meta($post->ID, '_comment', 1)) ?></td>
    </tr>
    </tbody>
</table>
