<p>
    <label for="fs_price"><?php _e( 'Price', 'fast-shop' ) ?></label>
    <br>
    <input type="text" id="fs_price" name="fs_price" value="<?php echo @get_post_meta($post->ID, 'fs_price', true); ?>" />
</p>
<p>
    <label for="fs_product_article"><?php _e( 'Article', 'fast-shop' ) ?></label>
    <br>
    <input type="text" id="fs_product_article" name="fs_product_article" value="<?php echo @get_post_meta($post->ID, 'fs_product_article', true); ?>" />
</p>
<p>
    <label for="fs_action_price"><?php _e( 'Promotional price', 'fast-shop' ) ?></label>
    <br>
    <input type="text" id="fs_action_price" name="fs_action_price" value="<?php echo @get_post_meta($post->ID, 'fs_action_price', true); ?>" />
</p>
<p>
    <label for="fs_wholesale_price"><?php _e( 'Wholesale price', 'fast-shop' ) ?></label><br>

    <input type="text" id="fs_wholesale_price" name="fs_wholesale_price" value="<?php echo @get_post_meta($post->ID, 'fs_wholesale_price', true); ?>" />
</p>

<p>
    <label for="fs_displayed_price"><?php _e( 'The displayed price', 'fast-shop' ) ?></label><br>

    <input type="text" id="fs_displayed_price" name="fs_displayed_price" value="<?php echo @get_post_meta($post->ID, 'fs_displayed_price', true); ?>" /><span>пример: "от %d %c за пару" (%d - заменяется на цену, %s - на валюту)</span>
</p>


<p> <label for="fs_remaining_amount"><?php _e( 'Запас товара на складе', 'fast-shop' ) ?></label><br>
    <?php $amount=get_post_meta($post->ID, 'fs_remaining_amount', true);
    $amount=empty($amount)?0:$amount;
    ?>
    <input type="text" id="fs_remaining_amount" name="fs_remaining_amount"  value="<?php echo $amount ?>" /></p>