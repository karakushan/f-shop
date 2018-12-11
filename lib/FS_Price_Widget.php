<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 30.04.2018
 * Time: 19:07
 */

namespace FS;

/*
 * Слайдер цены
 */

class FS_Price_Widget extends \WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'fs_price_widget',
            'Слайдер цены (F-SHOP)',
            array('description' => 'Позволяет вывести фильтр для фильтрации товара по атрибутам')
        );
    }

    /*
     * бэкэнд виджета
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        ?>
        <p>
            <label
                    for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title', 'f-shop') ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>


        <?php
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        fs_range_slider();
        echo $args['after_widget'];
    }

    /*
     * сохранение настроек виджета
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }
}