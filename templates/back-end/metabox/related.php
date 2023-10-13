<p class="add-related-sect">
    <label for="fs_product_article"><?php esc_html_e('Related products', 'f-shop') ?></label>
    <br>
    <button type="button" class="add-rell" data-fs-action="enabled-select"><?php esc_html_e('add', 'f-shop') ?></button>
    <select name="fs_related_category[]" data-post="<?php echo esc_attr() ?>" style="display: none"
            data-fs-action="get_taxonomy_posts">
        <option value=""><?php esc_html_e('Select a category', 'f-shop'); ?></option>
        <?php $categories = get_terms(array('taxonomy' => 'catalog', 'hide_empty' => false));
        if ($categories): ?>
            <?php foreach ($categories as $key => $category): ?>
                <option value="<?php echo esc_attr($category->term_id) ?>"><?php echo esc_html($category->name) ?></option>
            <?php endforeach ?>
        <?php endif ?>
    </select>

</p>
<ol class="related-wrap">
    <?php $related_products = get_post_meta($post->ID, $this->config->meta['related_products'], 1); ?>
    <?php if ($related_products) {
        foreach ($related_products as $key => $related_product) {
            echo '<li class="single-rel">';
            echo '<span>' . esc_html(get_the_title($related_product)) . '</span> <button type="button" data-fs-action="delete_parents" class="related-delete" data-target=".single-rel">' . esc_html__('remove', 'f-shop') . '</button>';
            echo '<input type="hidden" name="fs_related_products[]" value="' . esc_attr($related_product) . '">';
            echo '</li>';
        }
    } ?>
</ol>