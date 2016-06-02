<?php /**
* 
*/
class FS_Post_Types
{
	public $types=array(
		'reviews'=>array(
			'name'=>'Отзывы',
			'singular_name'=>'отзыв',
			'menu_icon' =>'dashicons-thumbs-up',
			'exclude_from_search' =>true,
			'taxonomies' => array(),
			'supports' => array('title', 'editor', 'excerpt','thumbnail','comments')


			));
	
	function __construct()
	{
		add_action('init', array(&$this, 'init'));
		
	}

	public function init()
	{
		$this->create_types();
	}


	public function create_types()
	{
		foreach ($this->types as $name=>$type) {
			$this->create_post_type($name,$type);
		}
	}

	public function create_post_type($name,$type)
	{
		register_post_type($name,
			array(
				'labels' => array(
					'name' =>$type['name'],
					'singular_name' =>$type['singular_name'],
					'add_new'=>'Добавить '.$type['singular_name'],
					'add_new_item'=>'Добавить '.$type['singular_name'],
					'edit_item'=>'Изменить '.$type['singular_name'],
					),
				'public' => true,
				'show_in_menu' =>true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'capability_type' => 'post',
				'menu_icon' =>$type['menu_icon'],
				'map_meta_cap' => true,
				'show_in_nav_menus' => true,
				'menu_position' =>null,
				'can_export' => true,
				'has_archive' => true,
				'rewrite'             => true,
				'query_var'           => true,
				'taxonomies' => $type['taxonomies'],
				'description' => __("Здесь размещены товары вашего сайта."),
				'supports' => $type['supports' ],
				'exclude_from_search'=>$type['exclude_from_search']
				)
			);
	}


} ?>