<table> 
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="meta_a">Цена</label>
        </th>
        <td>
            <input type="text" id="fs_price" name="fs_price" value="<?php echo @get_post_meta($post->ID, 'fs_price', true); ?>" />
        </td>
    </tr>
    <tr valign="top">
        <th class="metabox_label_column">
            <label for="fs_displayed_price">Отображаемая цена</label>
        </th>
        <td>
            <input type="text" id="fs_displayed_price" name="fs_displayed_price" value="<?php echo @get_post_meta($post->ID, 'fs_displayed_price', true); ?>" /><span>пример: "от %d %c за пару" (%d - заменяется на цену, %s - на валюту)</span>
        </td>
    </tr>    
   <!--  <tr valign="top">
        <th class="metabox_label_column">
            <label for="meta_a">Наличие на складе</label>
        </th>
        <td>
            <input type="checkbox" id="fs_availability" name="fs_availability" <?php checked( 1, get_post_meta($post->ID, 'fs_availability', true) ); ?> value="1" />
        </td>
    </tr> -->        
</table>
