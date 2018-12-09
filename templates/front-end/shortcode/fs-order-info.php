<div class="<?php echo esc_attr($class) ?>">
    <h2><?php esc_html(sprintf('Detailed information about the order number %d', 'f-shop'), $order_id) ?></h2>
    <h3><?php esc_html_e('Purchased goods', 'f-shop') ?>:</h3>
    <div class="table-responsive">
        <table class="table ">
            <thead style="thead-dark">
            <tr>
                <th>#<?php esc_html_e('ID', 'f-shop') ?></th>
                <th><?php esc_html_e('Photo', 'f-shop') ?></th>
                <th><?php esc_html_e('Title', 'f-shop') ?></th>
                <th><?php esc_html_e('Price', 'f-shop') ?></th>
                <th><?php esc_html_e('Quantity', 'f-shop') ?></th>
                <th><?php esc_html_e('Cost', 'f-shop') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($order->items)): ?>
                <?php foreach ($order->items as $id => $item): ?>
                    <tr>
                        <td><?php echo esc_html($id) ?></td>
                        <td class="thumb"><?php if (has_post_thumbnail($id))
                                echo get_the_post_thumbnail($id) ?></td>
                        <td><a href="<?php the_permalink($id) ?>" target="_blank"><?php echo get_the_title($id) ?></a>
                        </td>
                        <td><?php fs_the_price($id) ?></td>
                        <td><?php echo esc_html($item['count']) ?></td>
                        <td><?php echo fs_row_price($id, $item['count']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tfoot>
            <tr>
                <td colspan="5"><?php esc_html_e('Total Cost', 'f-shop') ?></td>
                <td><?php echo esc_html($order->sum) ?><?php echo esc_html(fs_currency()) ?></td>
            </tr>
            </tfoot>
            </tbody>
        </table>
    </div>
    <h3><?php esc_html_e('Contact details', 'f-shop') ?>:</h3>
    <ul class="<?php echo esc_attr($class) ?>-contacts">
        <li>
            <span><?php esc_html_e('First Name', 'f-shop') ?>: </span>
            <?php echo esc_html($order->user['first_name']); ?>
        </li>
        <li>
            <span><?php esc_html_e('Last name', 'f-shop') ?>: </span>
            <?php echo esc_html($order->user['last_name']); ?>
        </li>
        <li>
            <span><?php esc_html_e('E-mail', 'f-shop') ?>: </span>
            <?php echo esc_html($order->user['email']); ?>
        </li>
        <li>
            <span> <?php esc_html_e('Phone number', 'f-shop') ?>: </span>
            <?php echo esc_html($order->user['phone']); ?>
        </li>
        <li>
            <span><?php esc_html_e('City', 'f-shop') ?>: </span>
            <?php echo esc_html($order->user['city']); ?>
        </li>
        <li>
            <span> <?php esc_html_e('Delivery type', 'f-shop') ?>: </span>
            <?php echo esc_html($order->delivery['method']) ?>
        </li>
        <li>
            <span><?php esc_html_e('Type of payment', 'f-shop') ?>: </span>
            <?php echo esc_html($order->payment) ?> <a href="<?php echo esc_url($order->payment_link) ?>"
                                                       class="btn btn-secondary btn-sm"><?php esc_html_e('pay now', 'f-shop') ?></a>
        </li>
    </ul>
</div>