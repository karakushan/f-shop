<?php

/**
 * Button field template.
 *
 * @var array $args An array of arguments for the button field.
 * @var string $name The name attribute for the button.
 *
 * This template renders a button element with various attributes.
 * - The button's type is set to "button".
 * - Attributes such as name, id, class, value, placeholder, and title are populated from the $args array.
 * - The button can be marked as required or readonly based on $args.
 * - The label for the button is specified by $args['label'].
 */
do_action('qm/debug', $args);
?>
<button style="display:flex; align-items:center; gap:6px" x-data="{loading: false}" type="button"
        name="<?php echo esc_attr($name) ?>"
        id="<?php echo esc_attr($args['id']) ?>"
        class="<?php echo esc_attr($args['class']) ?>"
        x-on:click.prevent="loading=true; Alpine.store('FS').post('<?php echo esc_attr($args['value']) ?>').then(response => {
            if (response.success) {
                console.log(response);
            }
        }).finally(() => loading = false)"
        title="<?php echo esc_attr($args['title']) ?>"
> <?php echo esc_html($args['text'] ?? $args['label']) ?>

    <img x-show="loading" width="20" src="<?php echo esc_url(FS_PLUGIN_URL) ?>/assets/img/loader-circle.svg" alt="">
</button>

