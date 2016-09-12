<p>
    <label for="fs_price">Цена</label>
    <br>
    <input type="number" id="fs_price" name="fs_price" value="<?php echo @get_post_meta($post->ID, 'fs_price', true); ?>" />
</p>
<p>
    <label for="fs_wholesale_price">Оптовая цена</label><br>

    <input type="text" id="fs_wholesale_price" name="fs_wholesale_price" value="<?php echo @get_post_meta($post->ID, 'fs_wholesale_price', true); ?>" />
</p>

<p>
    <label for="fs_displayed_price">Отображаемая цена</label><br>

    <input type="text" id="fs_displayed_price" name="fs_displayed_price" value="<?php echo @get_post_meta($post->ID, 'fs_displayed_price', true); ?>" /><span>пример: "от %d %c за пару" (%d - заменяется на цену, %s - на валюту)</span>
</p>


<p> <label for="fs_availability">Наличие на складе</label><br>
    <?php $fs_availability=(!get_post_meta($post->ID,'fs_availability',1)?0:1); ?>
    <input type="checkbox" id="fs_availability" name="fs_availability" <?php checked( 1,$fs_availability); ?> value="1" /></p>