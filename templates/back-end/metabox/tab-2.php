<h3>Галерея товара</h3>
<p>Для выбора нескольких изображений одновременно, из медиа-библиотеки, нажмите "Ctrl" на клавиатуре и кликните по
  выбираемым изображениям.</p>
<p>Вы также можете перетаскивать изображения для изменения позиции, например в слайдере товара.</p>
<div class="fs-field-row clearfix">
  <button type="button" class="fs-button" id="fs-add-gallery">Добавить изображения</button>
</div>
<?php
$gallery = fs_gallery_images_ids( $post_id = 0 );
//print_r($gallery)
?>
<div class="fs-field-row fs-gallery clearfix">
  <div class="fs-grid fs-sortable-items" id="fs-gallery-wrapper">
	  <?php if ( $gallery ) ?>
	  <?php foreach ( $gallery as $key => $img ): ?>
		  <?php $image_attributes = wp_get_attachment_image_src( $img, 'medium' );
		  $src                    = $image_attributes[0]; ?>
        <div class="fs-col-4" draggable="true" style="background-image: url(<?php echo $src ?>);">
          <div class="fs-remove-img" title="<?php _e( 'Remove from gallery', 'fast-shop' ) ?>"></div>
          <input type="hidden" name="fs_galery[]" value="<?php echo $img ?>">
        </div>
	  <?php endforeach ?>
  </div>
</div>
