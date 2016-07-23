<?php 
namespace FS;
/**
*  Обработка POST или GET запросов
*/
class FS_Action_Class
{
	
	function __construct()
	{
		add_action('init', array(&$this,'fs_catch_action'), 2);
	}
	public function fs_catch_action()
	{
		if (isset($_REQUEST['fs_action'])) {
			if (!wp_verify_nonce($_REQUEST['_wpnonce'],'fs_action')) exit();
			$action=$_REQUEST['fs_action'];
			switch ($action) {
				//добавляем группу атрибутов
				case 'fs-add-attr-group':
				if (!isset($_POST['group-name'])) return;
				$attr=esc_sql($_POST['group-name']);
				$attr_key=esc_sql($_POST['group-name-en']);
				$fs_atributes=get_option('fs-attr-group');
				if ($fs_atributes) {
					foreach ($fs_atributes as $fs_atr) {
						$atribute[$fs_atr['slug']]=array(
							'slug'=>$fs_atr['slug'],
							'title'=>$fs_atr['title'],
							'attributes'=>$fs_atr['attributes']
							);
					}
					$atribute[$attr_key]=array(
						'slug'=>$attr_key,
						'title'=>$attr,
						'attributes'=>array()
						);
				}else{
					$atribute[$attr_key]=array(
						'slug'=>$attr_key,
						'title'=>$attr,
						'attributes'=>array()
						);
				}
				$opt_upd=update_option('fs-attr-group',$atribute);
				if ($opt_upd) {
					add_action('admin_notices', function(){
						echo '<div class="updated"><p>Группа атрибутов успешно добавлена.</p></div>';
					});
				}

				break;

				case 'fs-add-attr':
				if (!isset($_POST['attr-name']) && !isset($_POST['group-name'])) return;
				$fs_atributes=get_option('fs-attr-group');
				if ($fs_atributes) {
					$attr_name=esc_sql($_POST['attr-name']);
					$attr_slug=esc_sql($_POST['attr-slug']);
					$attr_group=esc_sql($_POST['group-name']);
					foreach ($fs_atributes as $fs_atr) {
						if ($fs_atr['slug']==$attr_group) {
							$fs_atr['attributes'][$attr_slug]=$attr_name;
						}
						$atribute[$fs_atr['slug']]=array(
							'slug'=>$fs_atr['slug'],
							'title'=>$fs_atr['title'],
							'attributes'=>$fs_atr['attributes']
							);
					}

					$opt_upd=update_option('fs-attr-group',$atribute);
					if ($opt_upd) {
						add_action('admin_notices', function(){
							echo '<div class="updated"><p>Атрибут товара успешно добавлен.</p></div>';
						});
					}
				}

				break;

				case 'fs-delete-group':
				if (!isset($_REQUEST['group-name'])) return;
				$fs_atributes=get_option('fs-attr-group');
			
				if ($fs_atributes) {
					foreach ($fs_atributes as $fs_atr) {

						if ($fs_atr['slug']==$_REQUEST['group-name']) continue;
						
						$atribute[$fs_atr['slug']]=array(
							'slug'=>$fs_atr['slug'],
							'title'=>$fs_atr['title'],
							'attributes'=>$fs_atr['attributes']
							);
					}

					$opt_upd=update_option('fs-attr-group',$atribute);
					if ($opt_upd) {
						add_action('admin_notices', function(){
							echo '<div class="updated"><p>Группа атрибутов успешно удалена.</p></div>';
						});
					}
				}


				break;

				default:
				exit;
				break;
			}
		}

		
	}
}