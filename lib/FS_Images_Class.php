<?php

namespace FS;

/**
 * Класс работы с изображениями магазина
 */
class FS_Images_Class
{
    protected $config;
    public $image_big_width = 9999;// ширина большого изображения слайдера
    public $image_big_height = 330;// высота большого изображения слайдера
    public $image_small_width = 9999; // ширина маленького изображения слайдера
    public $image_small_height = 108;// высота маленького изображения слайдера
    public $image_big_name = 'fs_gallery_big';// название большого изображения слайдера
    public $image_small_name = 'fs_gallery_small';// название маленького изображения слайдера
    public $image_crop = false;// использовать мягкое кадрирование

    function __construct()
    {
        $this->config = new FS_Config();
    }

    /**
     * @param int $post_id
     *
     * @return bool|string
     */
    public function fs_galery_list($post_id = 0)
    {
        global $post;
        $product_class = new FS_Product_Class();
        $variation = $product_class->get_product_variations($post_id);

        $post_id = empty($post_id) ? $post->ID : $post_id;
        $width = fs_option('gallery_img_width', 300);
        $height = fs_option('gallery_img_height', 400);
        $image_placeholder = fs_option('image_placeholder', 'holder.js/' . $width . 'x' . $height);
        $galerys = $this->fs_galery_images($post_id);
        $images_n = '';
        $alt = get_the_title($post_id);

        if (!empty($variation[0]['gallery'])) {
            $galerys = $variation[0]['gallery'];
        }

        if ($galerys) {
            foreach ($galerys as $atach_id) {
                $image = wp_get_attachment_image_src($atach_id, 'full');
                if (!$image) {
                    continue;
                }
                $image = $image[0];
                $images_n .= "<li data-thumb=\"$image\"  data-src=\"$image\" style=\"background_image( $image )\"><a href=\"$image\" data-lightbox=\"roadtrip\" data-title=\"" . get_the_title($post_id) . "\"><img src=\"$image\" alt=\"$alt\" itemprop=\"image\" data-zoom-image=\"$image\"></a></li>";
            }
        }

        if (empty($images_n)) {
            $images_n .= "<li data-thumb=\"$image_placeholder\" data-src=\"$image_placeholder\" style=\"background_image($image_placeholder)\"><img src=\"$image_placeholder\" alt=\"" . get_the_title($post_id) . "\" itemprop=\"image\" width=\"100%\" data-zoom-image=\"$image_placeholder\"></li>";
        }

        return apply_filters('fs_galery_list', $images_n, $post_id);
    }

    /**
     * @param integer $post_id - id записи
     * @param array $args - массив аргументов: http://sachinchoolur.github.io/lightslider/settings.html
     */
    public function lightslider($post_id = 0, $args = array())
    {
        $default = array(
            "gallery" => true,
            "item" => 1,
            "vertical" => false,
            "thumbItem" => 3,
            "prevHtml" => '',
            "nextHtml" => ''
        );
        $args = wp_parse_args($args, $default);
        echo "<script>";
        echo "var fs_lightslider_options=" . json_encode($args);
        echo "</script>";
        echo "<ul id=\"product_slider\">";
        echo $this->fs_galery_list($post_id);
        echo "</ul>";
    }

    /**
     * получаем url изображений галереи в массиве
     *
     * @param  integer $post_id - id записи
     * @param  bool $thumbnail - включать ли в галерею установленную миниатюру поста
     *
     * @return array          список id вложений в массиве
     */
    public function fs_galery_images($post_id = 0, $thumbnail = true)
    {

        $gallery_img = array();
        $post_id = fs_get_product_id($post_id);
        $gallery = get_post_meta($post_id, 'fs_galery', false);
        $thumb_id = intval(get_post_thumbnail_id($post_id));

        // добавляем в галерею изображение миниатюры поста, конечно если $thumbnail верно
        if ($thumb_id && $thumbnail == true) {
            $gallery_img[] = $thumb_id;
        }
        if ($gallery) {
            $images = !empty($gallery[0]) ? $gallery[0] : array();
            if ($images) {
                foreach ($images as $key => $image) {
                    $gallery_img[] = $image;
                }
            }
        }

        $gallery_img = array_unique($gallery_img);

        return apply_filters('fs_galery_images', $gallery_img, $post_id);
    }

}