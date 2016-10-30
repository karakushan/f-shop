<?php 
namespace FS;
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

			$args = array(
		'label'                 => __( 'Product categories', 'fast-shop' ), // определяется параметром $labels->name

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

			$labels_1=array(
				'add_new_item'=>'Добавить свойство/атрибут товара или группу свойств'
				);

			$args_1 = array(
		'label'                 => __( 'Product attributes', 'fast-shop' ), // определяется параметром $labels->name
		'labels'=>$labels_1,
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

			register_taxonomy('product-attributes', 'product', $args_1 );












			$args_2 = array(
		'label'                 => __( 'Payment methods', 'fast-shop' ), // определяется параметром $labels->name
		'labels'=>array(
			'add_new_item'=>__( 'To add a payment method', 'fast-shop' ),
			'not_found'=>__( 'There are no available payment methods', 'fast-shop' )
			),
		'public'                => true,
		'show_ui'               => true, // равен аргументу public
		'show_tagcloud'         => false, // равен аргументу show_ui
		'hierarchical'          => false,
		'show_in_nav_menus'  => false,
		
		'update_count_callback' => '',
		'rewrite' =>true,
		'publicly_queryable'=>false,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true, // название параметра запроса
		'capabilities'          => array(),
		'meta_box_cb'           => false, // callback функция. Отвечает за html код метабокса (с версии 3.8): post_categories_meta_box или post_tags_meta_box. Если указать false, то метабокс будет отключен вообще
		'show_admin_column'     => false, // Позволить или нет авто-создание колонки таксономии в таблице ассоциированного типа записи. (с версии 3.5)
		'_builtin'              => false,
		'show_in_quick_edit'    => true, // по умолчанию значение show_ui
		);	

			register_taxonomy('fs-payment-methods', 'product', $args_2 );	

			$args_3 = array(
		'label'                 => __( 'Delivery methods', 'fast-shop' ), // определяется параметром $labels->name
		'labels'=>array(
			'add_new_item'=>__( 'To add a shipping method', 'fast-shop' ),
			'not_found'=>__( 'Not found shipping methods', 'fast-shop' )
			),
		'public'                => true,
		'show_ui'               => true, // равен аргументу public
		'show_tagcloud'         => true, // равен аргументу show_ui
		'hierarchical'          => false,
		'show_in_nav_menus'  => false,
		'show_tagcloud'  => true,
		'update_count_callback' => '',
		'rewrite' =>true,
		'publicly_queryable'=>false,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true, // название параметра запроса
		'capabilities'          => array(),
		'meta_box_cb'           => false, // callback функция. Отвечает за html код метабокса (с версии 3.8): post_categories_meta_box или post_tags_meta_box. Если указать false, то метабокс будет отключен вообще
		'show_admin_column'     => false, // Позволить или нет авто-создание колонки таксономии в таблице ассоциированного типа записи. (с версии 3.5)
		'_builtin'              => false,
		'show_in_quick_edit'    => true, // по умолчанию значение show_ui
		);	

			register_taxonomy('fs-delivery-methods', 'product', $args_3 );

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