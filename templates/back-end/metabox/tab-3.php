<p>
    <label for="fs_actions"><?php _e( 'Enable/disable label stock on the website', 'fast-shop' ) ?></label>
    <br>
    <?php $action=(!get_post_meta($post->ID,'fs_actions',1)?0:1); ?>

    <input type="checkbox" id="fs_actions" name="fs_actions" <?php checked( 1,$action); ?> value="1" />
</p>

<p>
    <label for="fs_page_action"><?php _e( 'The link to the promotions page', 'fast-shop' ) ?></label>
    <br>
    <input type="text" id="fs_page_action" name="fs_page_action" value="<?php echo @get_post_meta($post->ID, 'fs_page_action',1); ?>" />

</p>