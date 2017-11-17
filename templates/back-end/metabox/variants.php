<h3>Варианты покупки</h3>
<p>Вы можете настроить условия при которых цена будет изменятся.</p>
<?php
global $fs_config;
$variated_on = get_post_meta( $post->ID, 'fs_variated_on', 1 );
?>
<p><input type="checkbox" name="fs_variated_on" value="1" id="fs_variated" <?php checked( $variated_on, 1 ) ?>> <label
    for="fs_variated" style="display: inline-block;">Сделать товар вариативным</label></p>
<?php if ( $variated_on ): ?>
<?php
    $variants = get_post_meta( $post->ID, 'fs_variant', 0 );
    $variants_price = get_post_meta( $post->ID, 'fs_variant_price', 0 );
    if (!empty($variants_price[0])){
	    $variants_price=$variants_price[0];
    }else{
	    $variants_price='';
    }
?>
  <button type="button" class="button" id="fs-add-variant">добавить вариант</button>
  <div id="fs-variants-wrapper">
	  <?php if ( ! empty( $variants[0] ) ): ?>
		  <?php foreach ( $variants[0] as $key => $variant ): ?>
          <div class="fs-rule" data-index="<?php echo $key  ?>">
            <a href="#" class="fs-remove-variant">удалить вариант</a>
            <p>
              <label for="">Вариант №<?php echo $key + 1 ?></label>
				<?php if ( is_array( $variant ) ): ?>
					<?php foreach ( $variant as $k => $v ): ?>
						<?php
						$args = array(
							'show_option_all'  => 'Свойство товара',
							'show_option_none' => '',
							'orderby'          => 'ID',
							'order'            => 'ASC',
							'show_last_update' => 0,
							'show_count'       => 0,
							'hide_empty'       => 0,
							'child_of'         => 0,
							'exclude'          => '',
							'echo'             => 1,
							'selected'         => $v,
							'hierarchical'     => 1,
							'name'             => 'fs_variant[' . $key . '][' . $k . ']',
							'id'               => '',
							'class'            => 'fs_select_variant',
							'depth'            => 0,
							'tab_index'        => 0,
							'taxonomy'         => $fs_config->data['product_att_taxonomy'],
							'hide_if_empty'    => false,

						);

						wp_dropdown_categories( $args ); ?>
					<?php endforeach; ?>


				<?php endif; ?>
              <button type="button" class="button-small" data-fs-element="clone-att">ещё свойство</button>
            </p>
            <p>
              <label for="">Цена</label>
              <input type="text" name="fs_variant_price[<?php echo $key ?>]" value="<?php echo $variants_price[$key] ?>" class="fs_variant_price">
            </p>
          </div>
		  <?php endforeach; ?>
	  <?php endif; ?>
  </div>
<?php endif ?>