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
    public function fs_galery_list($post_id='', $size=array(90,90))
	{

		$images_n='';

		if ($post_id=='') {
            global $post;
			$post_id=$post->ID;
			$images=array();
		}
		$galery=get_post_meta( $post_id, $this->config->meta['gallery'], false);
		$galerys=isset($galery[0])?$galery[0]:array();

		if (has_post_thumbnail( $post_id)) {
			$atach_id = get_post_thumbnail_id($post_id);
			$image= wp_get_attachment_image_src($atach_id, $size);
			$image_full= wp_get_attachment_image_src( $atach_id,'full');

			$images_n.= "<li data-thumb=\"$image[0]\" data-src=\"$image_full[0]\"><a href=\"$image_full[0]\"  data-lightbox=\"roadtrip\" data-title=\"".get_the_title($post_id)."\"><img src=\"$image_full[0]\" width=\"100%\"></a></li>";
		}

		if ($galerys) {
			foreach ($galerys as $atach_id) {
				$image= wp_get_attachment_image_src($atach_id, $size);
				if ($image=='' || $image=='undefined') continue;
				$image_full= wp_get_attachment_image_src( $atach_id,'full');
				$images_n.= "<li data-thumb=\"$image[0]\" data-src=\"$image_full[0]\"><a href=\"$image_full[0]\" data-lightbox=\"roadtrip\" data-title=\"".get_the_title($post_id)."\"><img src=\"$image_full[0]\" width=\"100%\"></a></li>";
			}
			
		}

		if ($images_n=='') {
			return false;
		}else{
			return $images_n;
		}
	}	

	public function fs_galery_images($post_id='',$size='')
	{
		$images=array();
		global $post;
		if ($post_id=='') {
			$post_id=$post->ID;
			$images=array();
		}
		$galery=get_post_meta( $post_id, 'fs_galery', false);
		if (count($galery[0])) {
			foreach ($galery[0] as $img) {

				$image= wp_get_attachment_image_src( $img, $size);
				$images[]=$image[0];
			}
		}
		return $images;
	}

}