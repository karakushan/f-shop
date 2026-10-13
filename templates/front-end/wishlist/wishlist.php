<a href="<?php echo esc_url(fs_wishlist_url())  ?>" title="<?php  esc_attr_e('To wish list','f-shop') ?>">
    <span x-text="$store.FS?.wishlist?.count ?? <?php echo esc_attr($wishlist_count); ?>"><?php fs_wishlist_count() ?></span>
</a>
