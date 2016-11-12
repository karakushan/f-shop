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


<p> <label for="fs_availability"><?php _e( 'Instock', 'fast-shop' ) ?></label><br>
    <?php $fs_availability=(!get_post_meta($post->ID,'fs_availability',1)?0:1); ?>
    <input type="checkbox" id="fs_availability" name="fs_availability" <?php checked( 1,$fs_availability); ?> value="1" /></p>