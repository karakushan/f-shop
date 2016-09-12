<?php
    $fs_attr_post=get_post_meta($post->ID,'fs_attributes',false);
    $fs_atributes_post=(!empty($fs_attr_post[0])?$fs_attr_post[0]:array());
     $fs_atributes=get_option('fs-attr-group'); ?>
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