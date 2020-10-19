<table class="wp-list-table widefat fixed striped order-list">
    <thead>
    <tr>
        <th><?php use FS\FS_Config;

	        esc_html_e('ID', 'f-shop') ?></th>
        <th><?php esc_html_e('Photo', 'f-shop') ?></th>
        <th><?php esc_html_e('Title', 'f-shop') ?></th>
        <th><?php esc_html_e('SKU', 'f-shop') ?></th>
        <th><?php esc_html_e('Price', 'f-shop') ?></th>
        <th><?php esc_html_e('Quantity', 'f-shop') ?></th>
        <th><?php esc_html_e('Attributes', 'f-shop') ?></th>
        <th><?php esc_html_e('Cost', 'f-shop') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $variation_id => $product): ?>
        <?php
        $offer = fs_set_product($product);
        ?>
        <tr>
            <td><?php echo esc_attr($offer->id) ?></td>
            <td><a href="<?php echo esc_url($offer->permalink) ?>" target="_blank"
                   title="перейти к товару"><?php fs_product_thumbnail($offer->id) ?></a></td>
            <td><a href="<?php echo esc_url($offer->permalink) ?>" target="_blank"
                   title="перейти к товару"><strong><?php echo esc_attr($offer->title) ?></strong></a></td>
            <td><?php echo esc_attr($offer->sku) ?></td>
            <td><?php echo esc_attr($offer->price_display) ?>&nbsp;<?php echo esc_attr($offer->currency) ?></td>
            <td><?php echo esc_attr($offer->count) ?></td>
            <td>
                <?php
                $fs_config=new FS_Config();
                if (count($offer->attributes)) {
                    echo '<ul class="product-att">';
                    foreach ($offer->attributes as $att) {
                        echo '<li><b>' . esc_attr(apply_filters('the_title',$att->parent_name)) . '</b>: ' . esc_attr(apply_filters('the_title',$att->name)) . '</li>';
                    }
                    echo '</ul>';
                }
                ?>
            </td>
            <td><?php echo esc_attr($offer->cost_display) ?>&nbsp;<?php echo esc_attr($offer->currency) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="8"><h4 style="margin: 0;"><?php esc_html_e('Total cost', 'f-shop'); ?> <?php echo esc_html($amount) ?></h4></td>
    </tr>
    </tfoot>
</table>