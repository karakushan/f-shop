<?php
    $fs_attr_post=get_post_meta($post->ID,'fs_attributes_post',false);
    $fs_attr_post=isset($fs_attr_post[0])?$fs_attr_post[0]:array();
    $fs_atributes_post=(!empty($fs_attr_post[0])?$fs_attr_post[0]:array());
     $fs_atributes_group=get_option('fs-attr-groups')!=false?get_option('fs-attr-groups'):array(); 
     $fs_atributes=get_option('fs-attributes')!=false?get_option('fs-attributes'):array(); ?>
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