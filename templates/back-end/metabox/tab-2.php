<?php  $mft=get_post_meta($post->ID, $this->config->meta['gallery'], false); ?>
<button type="button" id="new_image">+ добавить изображение</button>
<div class="row-images" id="mmf-1">

    <div class="row-images">
        
    </div>
    <?php if ($mft): ?>
        <?php for ($i=0; $i<count($mft[0]);$i++){
            $image_attributes = wp_get_attachment_image_src( $mft[0][$i], array(164, 133) );
            $src = $image_attributes[0]; ?>
            <div class="mmf-image" >
                <img src="<?php echo $src ?>" alt="" width="164" height="133" class="image-preview">
                <input type="hidden" name="fs_galery[]" value="<?php echo $mft[0][$i] ?>" class="img-url">
                <button type="button" class="upload-mft">Загрузить</button>
                <button type="button" class="remove-tr" onclick="btn_view(this)">удалить</button>
            </div>
            <?php
        } ?>
    <?php endif; ?>
</div>