
<p>
    <label for="fs_price"><?php _e( 'Price', 'fast-shop' ) ?></label>
    <br>
    <input type="text" id="fs_price" name="fs_price" value="<?php echo @get_post_meta($post->ID, 'fs_price', true); ?>" /><span class="tooltip">В качестве разделителя копеек можно использовать точку "." или запятую ","</span>
</p>

<p>
    <label for="fs_action_price"><?php _e( 'Promotional price', 'fast-shop' ) ?></label>
    <br>
    <input type="text" id="fs_action_price" name="fs_action_price" value="<?php echo @get_post_meta($post->ID, 'fs_action_price', true); ?>" /><span class="tooltip">Если значение не пустое, то перебивает базовую цену. Базовая цена отображается перечёркнутой.</span>
</p>
<p>
    <label for="fs_displayed_price"><?php _e( 'The displayed price', 'fast-shop' ) ?></label><br>

    <input type="text" id="fs_displayed_price" name="fs_displayed_price" value="<?php echo @get_post_meta($post->ID, 'fs_displayed_price', true); ?>" /><span>пример: "от %d %c за пару" (%d - заменяется на цену, %s - на валюту)</span>
</p>
