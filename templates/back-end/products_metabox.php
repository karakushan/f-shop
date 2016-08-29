<div id="fs-tabs" class="fs-metabox">
    <ul>
        <li><a href="#tabs-1">Цены</a></li>
        <li><a href="#tabs-2">Атрибуты</a></li>
        <li><a href="#tabs-3">Галерея</a></li>
        <li><a href="#tabs-4">Скидки и акции</a></li>

    </ul>
    <div id="tabs-1">



        <p>
            <label for="fs_price">Цена</label>
            <br>
            <input type="number" id="fs_price" name="fs_price" value="<?php echo @get_post_meta($post->ID, 'fs_price', true); ?>" />
        </p>
        <p>
            <label for="fs_wholesale_price">Оптовая цена</label><br>

            <input type="text" id="fs_wholesale_price" name="fs_wholesale_price" value="<?php echo @get_post_meta($post->ID, 'fs_wholesale_price', true); ?>" />
        </p>

        <p>
            <label for="fs_displayed_price">Отображаемая цена</label><br>

            <input type="text" id="fs_displayed_price" name="fs_displayed_price" value="<?php echo @get_post_meta($post->ID, 'fs_displayed_price', true); ?>" /><span>пример: "от %d %c за пару" (%d - заменяется на цену, %s - на валюту)</span>
        </p>


        <p> <label for="fs_availability">Наличие на складе</label><br>
            <?php $fs_availability=(!get_post_meta($post->ID,'fs_availability',1)?0:1); ?>
            <input type="checkbox" id="fs_availability" name="fs_availability" <?php checked( 1,$fs_availability); ?> value="1" /></p>
    </div>
    <div id="tabs-2">
        <?php
        $fs_attr_post=get_post_meta($post->ID,'fs_attributes',false);
        $fs_atributes_post=(!empty($fs_attr_post[0])?$fs_attr_post[0]:array());
        ?>
        <?php // echo "<pre>"; ?>
        <?php //  print_r($fs_atributes_post) ?>
        <?php // echo "</pre>"; ?>
        <?php $fs_atributes=get_option('fs-attr-group'); ?>
        <?php // echo "<pre>"; ?>
        <?php // print_r($fs_atributes) ?>
        <?php // echo "</pre>"; ?>
        <?php if ($fs_atributes): ?>
            <ul>
                <?php foreach ($fs_atributes as $fs_atr): ?>
                    <li class="fs-attr"><h3><?php echo $fs_atr['title'] ?></h3>

                        <?php if ($fs_atr['attributes']): ?>
                            <table>
                                <?php foreach ($fs_atr['attributes'] as $key => $fs_attr): ?>
                                    <?php $curr_val=(isset($fs_atributes_post[$fs_atr['slug']][$key]) && $fs_atributes_post[$fs_atr['slug']][$key]==1 ? 1 : 0); ?>
                                    <tr>
                                        <td><?php echo $fs_attr ?></td>
                                        <td> <label for="<?php echo $key ?>_1">ON</label>
                                            <input type="radio" name="fs_attributes[<?php echo $fs_atr['slug'] ?>][<?php echo $key ?>]" value="1" id="<?php echo $key ?>_1" <?php checked($curr_val,1); ?>>
                                            <label for="<?php echo $key ?>_2">OFF</label>
                                            <input type="radio" name="fs_attributes[<?php echo $fs_atr['slug'] ?>][<?php echo $key ?>]" value="0" id="<?php echo $key ?>_2" <?php checked($curr_val,0); ?>></td>
                                    </tr>
                                <?php endforeach ?>
                            </table>
                        <?php endif ?>

                    </li>

                <?php endforeach ?>

            </ul>
        <?php endif ?>
    </div>
    <div id="tabs-3">
        <div class="row-images" id="mmf-1">
            <div class="row-images">
                <button type="button" id="new_image">+ добавить изображение</button>
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

    </div>
    <div id="tabs-4">
        <p>
            <label for="fs_actions">Установить акцию на товар</label>
            <br>
            <?php $action=(!get_post_meta($post->ID,'fs_actions',1)?0:1); ?>

            <input type="checkbox" id="fs_actions" name="fs_actions" <?php checked( 1,$action); ?> value="1" />
        </p>
        <p>
            <label for="fs_discount">Размер скидки</label><br>

            <input type="text" id="fs_discount" name="fs_discount" value="<?php echo @get_post_meta($post->ID, 'fs_discount', true); ?>" />
        </p>
        <p>
            <label for="fs_page_action">Ссылка на описание акции или скидки</label>
            <br>
            <input type="text" id="fs_page_action" name="fs_page_action" value="<?php echo @get_post_meta($post->ID, 'fs_page_action',1); ?>" />

        </p>
    </div>

</div>




