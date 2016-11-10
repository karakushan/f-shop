<?php 
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Класс работы с изображениями магазина
*/
class FS_Images_Class
{
	protected $config;
	
	function __construct()
	{
		$this->config=new FS_Config();
        add_action('init',array($this,'register_product_g_thumbnail'));

    }

    public function register_product_g_thumbnail()
    {
     if ( function_exists( 'add_image_size' ) ) {
        $width=fs_option('gallery_img_width',300);
        $height=fs_option('gallery_img_height',400);
        switch(fs_option('cutting_photos')){
            case 'cut_width_height':
            add_image_size( 'fs_gallery_big', $width, $height,true); 
            break;
            case 'cut_no':
            add_image_size( 'fs_gallery_big', $width, $height,false); 
            break;
            case 'cut_width':
            add_image_size( 'fs_gallery_big', $width, 9999,true); 
            break;
            case 'cut_height':
            add_image_size( 'fs_gallery_big', 9999, $height,true); 
            break;
            default:
            add_image_size( 'fs_gallery_big', $width, $height,false); 
            break;
        }
    }
}

    /**
     * @param string $post_id
     * @param array $size
     * @return bool|string
     */
    public function fs_galery_list($post_id='')
    {

    	$images_n='';
        $gallery_image='';
        $width=fs_option('gallery_img_width',300);
        $height=fs_option('gallery_img_height',400);
        $image_placeholder=fs_option('image_placeholder','holder.js/'.$width.'x'.$height);
        $galerys=$this->fs_galery_images($post_id);
        $images_n.=apply_filters('fs_first_gallery_image',$post_id,'fs_gallery_big');
        if ($galerys) {
          foreach ($galerys as $atach_id) {
            $image= wp_get_attachment_image_src($atach_id,'fs_gallery_big');
            $image_full= wp_get_attachment_image_src( $atach_id,'full');

            $images_n.= "<li data-thumb=\"$image[0]\" data-src=\"$image_full[0]\"><a href=\"$image_full[0]\" data-lightbox=\"roadtrip\" data-title=\"".get_the_title($post_id)."\"><img src=\"$image[0]\" width=\"100%\"></a></li>";
        }
    }
    if (empty($images_n)) {
        $images_n.= "<li data-thumb=\"$image_placeholder\" data-src=\"$image_placeholder\"><img src=\"$image_placeholder\" width=\"100%\"></li>";
    }
    return $images_n;
}	

/**
 * получаем url изображений галереи в массиве
 * @param  integer $post_id - id записи
 * 
 * @return array          список url в массиве
 */
public function fs_galery_images($post_id=0)
{
	$images=array();
	global $post;
	$post_id=!empty($post_id)?(int)$post_id:$post->ID;
	$gallery=get_post_meta( $post_id, 'fs_galery', false);
	$images=!empty($gallery)?$gallery[0]:array();
	return apply_filters('fs_galery_images',$images,$post_id);
}

}