<h3>Дополнительные данные</h3>
<p>Здесь можно управлять дополнительными данными товара.</p>
<div class="fs-field-row clearfix">
  <label for="fs_product_article"><?php _e( 'Article', 'fast-shop' ) ?></label>
  <input type="text" name="<?php echo $this->config->meta['product_article'] ?>" id="fs_product_article"
         value="<?php echo fs_product_code(); ?>" id="price">
</div>
<div class="fs-field-row clearfix">
  <label for="fs_remaining_amount"><?php _e( 'Запас товара на складе', 'fast-shop' ) ?> <span>единиц</span></label>
  <input type="text" id="fs_remaining_amount" name="fs_remaining_amount" value="<?php echo fs_remaining_amount() ?>">
  <div class="fs-help">Укажите "0" если запас исчерпан. Пустое поле означает что управление запасами для товара
    отключено, и товар всегда в налиии!
  </div>
</div>
<div class="fs-field-row clearfix">

  <div class="checkbox-wrapper">
    <input type="checkbox" id="fs_exclude_archive"
           name="<?php echo esc_attr( $this->config->meta['exclude_archive'] ) ?>" <?php checked( get_post_meta( $post->ID, $this->config->meta['exclude_archive'], 1 ), 1 ) ?>
           value="1">
    <label for="fs_exclude_archive"><?php _e( 'Исключить из архива товаров', 'fast-shop' ) ?> </label>
  </div>
</div>
<div class="fs-field-row  clearfix">

  <div class="checkbox-wrapper"><input type="checkbox" id="fs_on_bestseller"
                                        name="<?php echo esc_attr( $this->config->meta['label_bestseller'] ) ?>" <?php checked( get_post_meta( $post->ID, $this->config->meta['label_bestseller'], 1 ), 1 ) ?>
                                        value="1"><label
      for="fs_on_bestseller"><?php _e( 'Включить метку "Хит продаж"', 'fast-shop' ) ?> </label></div>
</div>
<div class="fs-field-row clearfix">
  <div class="checkbox-wrapper">

    <input type="checkbox" id="fs_on_promotion"
           name="<?php echo esc_attr( $this->config->meta['label_promotion'] ) ?>" <?php checked( get_post_meta( $post->ID, $this->config->meta['label_promotion'], 1 ), 1 ) ?>
           value="1">
    <label
      for="fs_on_promotion"><?php _e( 'Включить метку "Акция"', 'fast-shop' ) ?> </label>
  </div>
</div>