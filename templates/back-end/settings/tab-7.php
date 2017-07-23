<div id="tabs-7">
	<h2>Настройки галереи в карточке товара</h2>
	<p>Внимание! Для работы слайдера необходимо, чтобы ваша тема поддерживала загрузку миниатюр.</p>
	<p>
		<label for="image_placeholder">Заглушка изображения
			<span>отображается если галерея не загружена</span></label><br>
		<input type="text" name="fs_option[image_placeholder]" id="image_placeholder"
		       value="<?php echo fs_option( 'image_placeholder' ) ?>">
	</p>
	<h3>Большое изображение</h3>

	<p>
		<label for="gallery_img_width">Ширина большого изображения</label><br>
		<input type="text" name="fs_option[gallery_big_width]" id="gallery_img_width"
		       value="<?php echo fs_option( 'gallery_big_width' ) ?>">
	</p>
	<p>
		<label for="gallery_img_height">Высота большого изображения</label><br>
		<input type="text" name="fs_option[gallery_big_height]" id="gallery_img_height"
		       value="<?php echo fs_option( 'gallery_big_height' ) ?>">
	</p>

	<h3>Маленькие изображения</h3>
	<p>
		<label for="gallery_img_width">Ширина маленького изображения</label><br>
		<input type="text" name="fs_option[gallery_small_width]" id="gallery_img_width"
		       value="<?php echo fs_option( 'gallery_small_width' ) ?>">
	</p>
	<p>
		<label for="gallery_img_height">Высота маленького изображения</label><br>
		<input type="text" name="fs_option[gallery_small_height]" id="gallery_img_height"
		       value="<?php echo fs_option( 'gallery_small_height' ) ?>">
	</p>

</div>