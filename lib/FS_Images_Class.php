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
    	$galerys=$this->fs_galery_images($post_id);
    	$images_n.=apply_filters('fs_first_gallery_image',$post_id,'full');
    	if ($galerys) {
    		foreach ($galerys as $atach_id) {
    		    if (is_numeric($atach_id)){
                    $gallery_image= wp_get_attachment_url( $atach_id);
                }else{
                    $gallery_image=$atach_id;
                }
    			$images_n.= "<li data-thumb=\"$gallery_image\" data-src=\"$gallery_image\"><a href=\"$gallery_image\" data-lightbox=\"roadtrip\" data-title=\"".get_the_title($post_id)."\"><img src=\"$gallery_image\" width=\"100%\"></a></li>";
    		}
    	}

    	if ($images_n=='' || empty($galerys)) {
    		return false;
    	}else{
    		return $images_n;
    	}
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
	$gallery=!empty($gallery)?$gallery[0]:array();
	if ($gallery) {
		foreach ($gallery as $img) {
			$images[]=wp_get_attachment_url($img);
		}
	}
	return apply_filters('fs_galery_images',$images,$post_id);
}

}