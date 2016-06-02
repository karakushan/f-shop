<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Класс работы с изображениями магазина
*/
class FS_Images_Class
{
	
	function __construct()
	{
		add_action('add_meta_boxes',array(&$this,'fs_galery_metabox') );
		add_action( 'save_post', array(&$this,'fs_galery_save') );
	}

	/* Добавляем блоки в основную колонку на страницах постов и пост. страниц */
	public function fs_galery_metabox() {
    		// Add this metabox to every selected post
		add_meta_box( 
			'fs_galery_metabox',
			'Галерея продукта',
			array(&$this, 'add_galery_products'),
			'products'

			);
	}

	public function fs_galery_list($post_id='',$size=array(90,90))
	{
		global $post;
		$images_n='';
		if ($post_id=='') {
			$post_id=$post->ID;
			$images=array();
		}
		$galery=get_post_meta( $post_id, 'fs_galery', false);
		$galerys=$galery[0];

		if (has_post_thumbnail( $post_id)) {
			$atach_id = get_post_thumbnail_id($post_id);
			$image= wp_get_attachment_image_src($atach_id, $size);
			$image_full= wp_get_attachment_image_src( $atach_id,'full');

			$images_n.= "<li data-thumb=\"$image[0]\" data-src=\"$image_full[0]\"><a href=\"$image_full[0]\"  data-lightbox=\"roadtrip\" data-title=\"".get_the_title($post_id)."\"><img src=\"$image_full[0]\" width=\"100%\"></a></li>";
		}

		if (count($galerys)) {
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

	public function add_galery_products()
	{
		// Используем nonce для верификации
		wp_nonce_field( plugin_basename(__FILE__), 'myplugin_noncename' );
		global $post;
		$post_id=$post->ID;

	// получаем данные мета полей.
		$mft=get_post_meta( $post_id, 'fs_galery', false);

	// echo "<pre>";
	// print_r($mft);
	// echo "</pre>";
		?>
		<div class="row-images" id="mmf-1">
			<?php if (count($mft[0])): ?>

				<?php for ($i=0; $i<count($mft[0]);$i++){ 
					$image_attributes = wp_get_attachment_image_src( $mft[0][$i], array(164, 133) );
					$src = $image_attributes[0];
					?>

					<div class="mmf-image" >
						<img src="<?php echo $src ?>" alt="" width="164" height="133" class="image-preview">
						<input type="hidden" name="mft[]" value="<?php echo $mft[0][$i] ?>" class="img-url">
						<button type="button" class="upload-mft">Загрузить</button>
						<button type="button" class="remove-tr" onclick="btn_view(this)">удалить</button>
					</div>
					<?php 
				} ?>
			<?php endif ?>
		</div>
		<div class="row-images">
			<button type="button" id="new_image">+ добавить изображение</button>
		</div>

		<?php
	}

	/* Сохраняем данные, когда пост сохраняется */
	function fs_galery_save( $post_id ) {
	// проверяем nonce нашей страницы, потому что save_post может быть вызван с другого места.
		if ( ! wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename(__FILE__) ) )
			return $post_id;

	// проверяем, если это автосохранение ничего не делаем с данными нашей формы.
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;

	// проверяем разрешено ли пользователю указывать эти данные
		if ( 'page' == $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		} elseif( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}


	// Обновляем данные в базе данных.
		update_post_meta( $post_id, 'fs_galery', $_POST['mft']);

	}


} ?>