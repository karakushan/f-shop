<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists('FS_Taxonomies_Class')) {
	/**
	* Создаём таксономии плагина
	*/
	class FS_Taxonomies_Class
	{
		
		function __construct()
		{
			add_action('init',array(&$this,'create_taxonomy') ,0);
		}

		function create_taxonomy(){
	// заголовки
			$labels = array(
				'name'              =>__( 'Product categories', 'fast-shop' ),
				'singular_name'     => 'Категория товара',
				'search_items'      => 'Искать категорию',
				'all_items'         => 'Все категории',
				'parent_item'       => 'Родительская категория',
				'parent_item_colon' => 'Родительская категория:',
				'edit_item'         => 'Изменить категорию',
				'update_item'       => 'Обновить категорию',
				'add_new_item'      => 'Добавить категорию',
				'new_item_name'     => 'Новая категория',
				'menu_name'         => 'Категории товара',
				); 
	// параметры
			$args = array(
		'label'                 => '', // определяется параметром $labels->name
		'labels'                => $labels,
		'public'                => true,
		'show_ui'               => true, // равен аргументу public
		'show_tagcloud'         => true, // равен аргументу show_ui
		'hierarchical'          => true,
		'show_in_nav_menus'  => true,
		'show_tagcloud'  => true,
		'update_count_callback' => '',
		'rewrite' =>true,

		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true, // название параметра запроса
		'capabilities'          => array(),
		'meta_box_cb'           => null, // callback функция. Отвечает за html код метабокса (с версии 3.8): post_categories_meta_box или post_tags_meta_box. Если указать false, то метабокс будет отключен вообще
		'show_admin_column'     => true, // Позволить или нет авто-создание колонки таксономии в таблице ассоциированного типа записи. (с версии 3.5)
		'_builtin'              => false,
		'show_in_quick_edit'    => true, // по умолчанию значение show_ui
		);	

			register_taxonomy('catalog', 'product', $args );
	

			
		}

//Получаем списком все категории продуктов
		public function get_product_terms($terms='catalog')
		{
			$args = array(
				'orderby'       => 'id', 
				'order'         => 'ASC',
				'hide_empty'    => false, 
				'exclude'       => array(), 
				'exclude_tree'  => array(), 
				'include'       => array(),
				'number'        => '', 
				'fields'        => 'all', 
				'slug'          => '', 
				'parent'         => 0,
				'hierarchical'  => true, 
				'child_of'      => 0, 
				'get'           => '', 
				'name__like'    => '',
				'pad_counts'    => false, 
				'offset'        => '', 
				'search'        => '', 
				'cache_domain'  => 'core',
				'name'          => '', 
				'childless'     => false,
				'update_term_meta_cache' => true, 
				'meta_query'    => '',
				); 
			$myterms = get_terms( array($terms), $args );
			if ($myterms ) {
				echo "<ul>";
				foreach( $myterms as $term ){
					$link=get_term_link( $term->term_id );
					$class="";
					if (strripos($link,$_SERVER['REQUEST_URI'])) {
						$class='class="active"';
					}
					echo "<li $class><a href=\"$link\">$term->name</a></li>";
				}
				echo "</ul>";

			}
			
		}
	}

	
}
?>