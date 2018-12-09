<h3>Атрибуты товара</h3>
<p>Здесь можно указать какими свойствами обладает товар.</p>
<?php
global $fs_config;
$attributes = get_the_terms($post->ID, $fs_config->data['product_att_taxonomy']);
$att_hierarchy = [];
if ($attributes) {
    foreach ($attributes as $att) {
        $att_hierarchy[$att->parent][] = $att;
    }
}
?>
<div class="fs-atts-list-table">
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>Атрибут</th>
            <th>Значение</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($att_hierarchy): ?>
            <?php foreach ($att_hierarchy as $k => $att_h): ?>
                <?php $parent = get_term($k, $fs_config->data['product_att_taxonomy']) ?>
                <tr>
                    <td><?php echo esc_html(apply_filters('the_title', $parent->name)) ?></td>
                    <td>

                        <ul class="fs-childs-list">   <?php foreach ($att_h as $child): ?>
                                <li><?php echo esc_html(apply_filters('the_title', $child->name)) ?> <a
                                            class="remove-att"
                                            title="<?php esc_attr_e('do I delete a property?', 'f-shop') ?>"
                                            data-action="remove-att"
                                            data-category-id="<?php echo esc_attr($child->term_id) ?>"
                                            data-product-id="<?php echo esc_attr($post->ID) ?>">удалить</a>
                                </li>

                            <?php endforeach; ?>

                        </ul>
                        <?php $args = array(
                            'show_option_all' => '',
                            'show_option_none' => '',
                            'orderby' => 'ID',
                            'order' => 'ASC',
                            'show_last_update' => 0,
                            'show_count' => 0,
                            'hide_empty' => 0,
                            'child_of' => $parent->term_id,
                            'exclude' => '',
                            'echo' => 1,
                            'selected' => 0,
                            'hierarchical' => 0,
                            'name' => 'cat',
                            'id' => 'name',
                            'class' => 'fs-select-att',
                            'depth' => 0,
                            'tab_index' => 0,
                            'taxonomy' => $fs_config->data['product_att_taxonomy'],
                            'hide_if_empty' => false,
                            'value_field' => 'term_id', // значение value e option
                            'required' => false,
                        );

                        wp_dropdown_categories($args); ?>
                        <button type="button" class="button button-secondary" data-fs-action="add-atts-from"
                                data-post="<?php echo esc_attr($post->ID) ?>">добавить
                        </button>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
