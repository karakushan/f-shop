<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 22.04.2018
 * Time: 14:36
 */

namespace FS;

class FS_Widget_Class
{

    public function __construct()
    {
        add_action('widgets_init', array($this, 'register_widgets'));
    }

    function register_widgets()
    {
        register_widget(\FS\Widget\Cart_Widget::class);
        register_widget(\FS\Widget\Attribute_Widget::class);
        register_widget(\FS\Widget\Reset_Filter_Widget::class);
        register_widget(\FS\Widget\Category_Widget::class);
        register_widget(\FS\Widget\Price_Widget::class);
        register_widget(\FS\Widget\Brand_Widget::class);
        register_widget(\FS\Widget\Availability_Widget::class);
    }
}