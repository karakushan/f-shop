<h3>Галерея товара</h3>
<p>Для выбора нескольких изображений одновременно, из медиа-библиотеки, нажмите "Ctrl" на клавиатуре и кликните по
  выбираемым изображениям.</p>
<div class="fs-field-row clearfix">
  <button type="button" class="button button-secondary" id="fs-add-gallery">Выбрать из медиатеки</button>
</div>
<?php
$gallery = fs_gallery_images_ids( $post_id = 0, false );
?>
<div class="fs-field-row fs-gallery clearfix">
  <p>Вы можете перетаскивать изображения для изменения позиции в галерее.</p>
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
<div class="fs-field-row">
  <div class="fs-upload-desc">Перетащите изображения с компьютера в пунктирную область ниже. Изображение будет загружено
    автоматически. Разрешённые форматы: png, jpeg, gif.
  </div>
  <div id="holder" class="fs-upload">
  </div>
  <p id="upload" class="hidden"><label>Функция Drag & drop не поддерживается вашим браузером. Попробуйте загрузить с
      помощью поля загрузки файлов:
      <br><input type="file"></label></p>
  <p id="filereader">File API & FileReader API не поддерживается</p>
  <p id="formdata">XHR2's FormData не поддерживается</p>
  <p id="progress">XHR2's upload progress не поддерживается</p>
  <p>Прогресс загрузки файлов: <br>
    <progress id="uploadprogress" max="100" value="0">0</progress>
  </p>
</div>