<h3><?php esc_html_e('Additional data', 'f-shop'); ?></h3>
<p><?php esc_html_e('Here you can manage additional product data.', 'f-shop'); ?>.</p>
<div class="fs-field-row clearfix">
    <label for="fs_product_article"><?php esc_html_e('Article', 'f-shop') ?></label>
    <input type="text" name="<?php echo esc_attr($this->config->meta['sku']) ?>" id="fs_product_article"
           value="<?php echo esc_attr(fs_product_code()); ?>" id="price">
</div>
<div class="fs-field-row clearfix">
    <label for="fs_remaining_amount"><?php esc_html_e('Stock in stock', 'f-shop') ?>
        <span><?php esc_html_e('unit', 'f-shop'); ?></span></label>
    <input type="text" id="fs_remaining_amount" name="fs_remaining_amount"
           value="<?php echo esc_attr(fs_remaining_amount()) ?>">
    <div class="fs-help"><?php esc_html_e('Enter "0" if stock is exhausted. An empty field means inventory control for the item.
        disabled, and the goods are always in the presence!', 'f-shop'); ?>
    </div>
</div>
<div class="fs-field-row clearfix">
    <div class="checkbox-wrapper">
        <input type="checkbox" id="fs_exclude_archive"
               name="<?php echo esc_attr($this->config->meta['exclude_archive']) ?>" <?php checked(get_post_meta($post->ID, $this->config->meta['exclude_archive'], 1), 1) ?>
               value="1">
        <label for="fs_exclude_archive"><?php esc_html_e('Исключить из архива товаров', 'f-shop') ?> </label>
    </div>
</div>
<div class="fs-field-row  clearfix">
    <div class="checkbox-wrapper">
        <input type="checkbox" id="fs_on_bestseller"
               name="<?php echo esc_attr($this->config->meta['label_bestseller']) ?>" <?php checked(get_post_meta($post->ID, $this->config->meta['label_bestseller'], 1), 1) ?>
               value="1">
        <label for="fs_on_bestseller"><?php esc_html_e('Включить метку "Хит продаж"', 'f-shop') ?> </label></div>
</div>
<div class="fs-field-row clearfix">
    <div class="checkbox-wrapper">
        <input type="checkbox" id="fs_on_promotion"
               name="<?php echo esc_attr($this->config->meta['label_promotion']) ?>" <?php checked(get_post_meta($post->ID, $this->config->meta['label_promotion'], 1), 1) ?>
               value="1">
        <label for="fs_on_promotion"><?php esc_html_e('Включить метку "Акция"', 'f-shop') ?> </label>
    </div>
</div>
<div class="fs-field-row clearfix">
    <div class="checkbox-wrapper">
        <input type="checkbox" id="<?php echo esc_attr($this->config->meta['label_novelty']) ?>"
               name="<?php echo esc_attr($this->config->meta['label_novelty']) ?>" <?php checked(get_post_meta($post->ID, $this->config->meta['label_novelty'], 1), 1) ?>
               value="1">
        <label for="<?php echo esc_attr($this->config->meta['label_novelty']) ?>"><?php esc_html_e('Включить метку "Новинка"', 'f-shop') ?> </label>
    </div>
</div>