<p>
    <label for="fs_product_article"><?php _e( 'Article', 'fast-shop' ) ?></label>
    <br>
    <input type="text" id="fs_product_article" name="<?php echo $this->config->meta['product_article'] ?>" value="<?php echo fs_product_code(); ?>" />
</p>
<p> <label for="fs_remaining_amount"><?php _e( 'Запас товара на складе', 'fast-shop' ) ?></label><br>
    <input type="number" min="0" id="fs_remaining_amount" name="fs_remaining_amount"  value="<?php echo fs_remaining_amount() ?>" /> <span class="tooltip">Укажите "0" если запас исчерпан. Пустое поле означает что управление запасами для товара отключено, и товар всегда в налиии!</span>
</p>