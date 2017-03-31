<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 27.02.2017
 * Time: 15:57
 */

namespace FS;


use ES_LIB\ES_config;

class FS_Form_Class
{

    /**
     * @param string $field_name  ключ поля в FS_Config::$form_fields
     * @param array $attr атрибуты input (class,id,value,checked)
     * @return string html код поля
     */
    function form_field($field_name = '', $attr = array())
    {
        $fields = FS_Config::$form_fields;
        $default = array(
            'class' => '',
            'id' => $field_name,
            'value' => '',
            'checked' => ''
        );
        $attr = wp_parse_args($attr, $default);
        $class = !empty($attr['class']) ? 'class="' . esc_attr($attr['class']) . '"' : '';
        $field = $fields[$field_name];
        $required = $field['required'] === true ? 'required' : '';
        $placeholder = $field['placeholder'] === true ? $field['label'] : '';
        $id = esc_attr($attr['id']);

        switch ($field['type']) {
            case 'text':
                $html = '<input type="text" name="' . $field_name . '" ' . $class . ' value="' . $attr['value'] . '" id="' . $id . '" ' . $placeholder . ' title="обязательное поле" ' . $required . '>';
                break;
            case 'email':
                $html = '<input type="email" name="' . $field_name . '" ' . $class . ' value="' . $attr['value'] . '" id="' . $id . '" ' . $placeholder . ' title="обязательное поле" ' . $required . '>';
                break;
            case 'tel':
                $html = '<input type="tel" name="' . $field_name . '" ' . $class . ' value="' . $attr['value'] . '" id="' . $id . '" ' . $placeholder . ' title="обязательное поле" ' . $required . '>';
                break;
            case 'radio':
                $html = '<input type="radio" name="' . $field_name . '" ' . $class . ' value="' . $attr['value'] . '" id="' . $id . '" ' . $placeholder . ' ' . $attr['checked'] . ' title="обязательное поле" ' . $required . '>';
                break;
            default:
                $html = '<input type="text" name="' . $field_name . '" ' . $class . ' value="' . $attr['value'] . '" id="' . $id . '" ' . $placeholder . ' title="обязательное поле" ' . $required . '>';

        }
        return $html;
    }
}
