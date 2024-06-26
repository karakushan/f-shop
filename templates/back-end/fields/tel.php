<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 01.07.2018
 * Time: 14:12
 *
 * @var string $name
 * @var array $args
 */
if (empty($args['placeholder']) && !empty($args['mask'])) {
    $args['placeholder'] = str_replace('9', '_', $args['mask']);
}
?>
<input type="tel" name="<?php echo esc_attr($name) ?>"
       id="<?php echo esc_attr($args['id']) ?>"
       class="<?php echo esc_attr($args['class']) ?>"
       value="<?php echo esc_html($args['value']) ?>"
       placeholder="<?php echo esc_attr($args['placeholder']) ?>"
       x-mask="<?php echo esc_attr($args['mask']) ?>"
    <?php echo fs_parse_attr($args['attributes'] ?? []) ?>
       title="<?php echo esc_attr($args['title']) ?>" <?php if ($args['required'])
    echo 'required' ?> <?php if (!empty($args['readonly']))
    echo 'readonly' ?>
>
