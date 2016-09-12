<p>
    <label for="fs_actions">Установить акцию на товар</label>
    <br>
    <?php $action=(!get_post_meta($post->ID,'fs_actions',1)?0:1); ?>

    <input type="checkbox" id="fs_actions" name="fs_actions" <?php checked( 1,$action); ?> value="1" />
</p>
<p>
    <label for="fs_discount">Размер скидки</label><br>

    <input type="text" id="fs_discount" name="fs_discount" value="<?php echo @get_post_meta($post->ID, 'fs_discount', true); ?>" />
</p>
<p>
    <label for="fs_page_action">Ссылка на описание акции или скидки</label>
    <br>
    <input type="text" id="fs_page_action" name="fs_page_action" value="<?php echo @get_post_meta($post->ID, 'fs_page_action',1); ?>" />

</p>