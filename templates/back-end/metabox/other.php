<h3><?php esc_html_e('Additional data', 'f-shop'); ?></h3>
<p><?php esc_html_e('Here you can manage additional product data.', 'f-shop'); ?>.</p>
<div class="fs-field-row clearfix">
    <label for="fs_product_article"><?php esc_html_e('SKU', 'f-shop') ?></label>
    <input type="text" name="<?php echo esc_attr(\FS\FS_Config::get_meta('sku')) ?>" id="fs_product_article"
           value="<?php echo esc_attr(fs_get_product_code()); ?>" id="price">
</div>
<div class="fs-field-row clearfix">
    <label for="fs_stock_status"><?php esc_html_e('Stock Status', 'f-shop') ?></label>
    <select id="fs_stock_status" name="fs_stock_status">
        <?php 
        $current_status = fs_get_stock_status();
        $statuses = fs_get_stock_statuses();
        foreach ($statuses as $value => $label): ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_status, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="fs-help"><?php esc_html_e('Select the current stock status of the product', 'f-shop'); ?></div>
</div>
<div class="fs-field-row clearfix">
    <div class="checkbox-wrapper">
        <input type="checkbox" id="fs_exclude_archive"
               name="<?php echo esc_attr(\FS\FS_Config::get_meta('exclude_archive')) ?>" <?php checked(get_post_meta($post->ID, \FS\FS_Config::get_meta('exclude_archive'), 1), 1) ?>
               value="1">
        <label for="fs_exclude_archive"><?php esc_html_e('Exclude from the archive of goods', 'f-shop') ?> </label>
    </div>
</div>
<div class="fs-field-row  clearfix">
    <div class="checkbox-wrapper">
        <input type="checkbox" id="fs_on_bestseller"
               name="<?php echo esc_attr(\FS\FS_Config::get_meta('label_bestseller')) ?>" <?php checked(get_post_meta($post->ID, \FS\FS_Config::get_meta('label_bestseller'), 1), 1) ?>
               value="1">
        <label for="fs_on_bestseller"><?php esc_html_e('Include the tag "Hit sales"', 'f-shop') ?> </label></div>
</div>
<div class="fs-field-row clearfix">
    <div class="checkbox-wrapper">
        <input type="checkbox" id="fs_on_promotion"
               name="<?php echo esc_attr(\FS\FS_Config::get_meta('label_promotion')) ?>" <?php checked(get_post_meta($post->ID, \FS\FS_Config::get_meta('label_promotion'), 1), 1) ?>
               value="1">
        <label for="fs_on_promotion"><?php esc_html_e('Include tag "Promotion"', 'f-shop') ?> </label>
    </div>
</div>
<div class="fs-field-row clearfix">
    <div class="checkbox-wrapper">
        <input type="checkbox" id="<?php echo esc_attr(\FS\FS_Config::get_meta('label_novelty')) ?>"
               name="<?php echo esc_attr(\FS\FS_Config::get_meta('label_novelty')) ?>" <?php checked(get_post_meta($post->ID, \FS\FS_Config::get_meta('label_novelty'), 1), 1) ?>
               value="1">
        <label for="<?php echo esc_attr(\FS\FS_Config::get_meta('label_novelty'))  ?>"><?php esc_html_e('Include tag "New"', 'f-shop') ?> </label>
    </div>
</div>