
                
                
                <h3>Галерея товара</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolorem eveniet dicta, accusantium!</p>
                <div class="fs-field-row clearfix">
                    <button type="button" class="fs-button" id="fs-add-gallery">Добавить изображения</button>
                </div>
                <div class="fs-field-row fs-gallery clearfix">
                    <div class="fs-grid">
                        <div class="fs-col-4">
                            <div class="fs-remove-img"></div>
                            <input type="hidden" name="">
                            <img src="img/image.svg" alt="">
                        </div>
                        <div class="fs-col-4">
                            <div class="fs-remove-img"></div>
                            <input type="hidden" name="">
                            <img src="img/image.svg" alt="">
                        </div><div class="fs-col-4">
                            <div class="fs-remove-img"></div>
                            <input type="hidden" name="">
                            <img src="img/image.svg" alt="">
                        </div>
                        <div class="fs-col-4">
                            <div class="fs-remove-img"></div>
                            <input type="hidden" name="">
                            <img src="img/image.svg" alt="">
                        </div>
                        
                    </div>
                    <div class="fs-grid">
                        <div class="fs-col-4">
                            <div class="fs-remove-img"></div>
                            <input type="hidden" name="">
                            <img src="img/image.svg" alt="">
                        </div>
                        <div class="fs-col-4">
                            <div class="fs-remove-img"></div>
                            <input type="hidden" name="">
                            <img src="img/image.svg" alt="">
                        </div><div class="fs-col-4">
                            <div class="fs-remove-img"></div>
                            <input type="hidden" name="">
                            <img src="img/image.svg" alt="">
                        </div>
                        <div class="fs-col-4">
                            <div class="fs-remove-img"></div>
                            <input type="hidden" name="">
                            <img src="img/image.svg" alt="">
                        </div>
                        
                    </div>
                </div>
                
                
                


            
<?php
$fs_attr_post=get_post_meta($post->ID,'fs_attributes_post',false);
print_r($fs_attr_post); ?>


<?php if ($fs_atributes_group): ?>
    <ul>
        <?php foreach ($fs_atributes_group as $key=>$fs_atr): ?>
            <li class="fs-attr"><h3><?php echo $fs_atr ?></h3>

                <?php if (!empty($fs_atributes[$key])): ?>
                    <table>
                        <?php foreach ( $fs_atributes[$key] as $key2=>$fs_attr): ?>
                            <?php $attr_value=isset($fs_attr_post[$key][$key2]) && $fs_attr_post[$key][$key2]==1?1:0; ?>
                            <tr>
                                <td><?php echo $fs_attr['name'] ?></td>
                                <td> <label for="<?php echo $key ?>_<?php echo $key2 ?>_1">ON</label>
                                    <input type="radio" name="fs_attributes_post[<?php echo $key ?>][<?php echo $key2 ?>]" value="1" id="<?php echo $key ?>_1" <?php checked($attr_value,1); ?>>
                                    <label for="<?php echo $key ?>_<?php echo $key2 ?>_2">OFF</label>
                                    <input type="radio" name="fs_attributes_post[<?php echo $key ?>][<?php echo $key2 ?>]" value="0" id="<?php echo $key ?>_<?php echo $key2 ?>_2" <?php checked($attr_value,0); ?>></td>
                                    <?php if ($fs_attr['type']=='image'): ?>
                                        <td><img src="<?php echo wp_get_attachment_url($fs_attr['value']) ?>" width="100" height="100"></td>
                                    <?php endif ?>
                                </tr>
                            <?php endforeach ?>
                        </table>
                    <?php endif ?>

                </li>

            <?php endforeach ?>

        </ul>
    <?php endif ?>