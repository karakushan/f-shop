<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class FS_Taxonomies_Class
 * @package FS
 * класс для создания служебных таксономий плагина
 */
class FS_Taxonomies_Class {

	public $config;
	public $product_taxonomy;

	function __construct() {
		add_action( 'init', array( $this, 'create_taxonomy' ) );
		add_filter( 'manage_fs-currencies_custom_column', array( $this, 'fs_currencies_column_content' ), 10, 3 );
		// добавляем колонку при просмотре списка терминов таксономии валют
		add_filter( 'manage_fs-currencies_custom_column', array( $this, 'fs_currencies_column_content' ), 10, 3 );
		add_filter( 'manage_edit-fs-currencies_columns', array( $this, 'add_fs_currencies_columns' ) );
	}

	/**
	 * Регистирует пользовательские таксономии
	 * @return array|mixed|void
	 */
	function shop_taxonomies() {
		$taxonomies=array(
			'catalog'             => array(
				'object_type'        => 'product',
				'label'              => __( 'Product categories', 'fast-shop' ),
				'labels'             => array(
					'name'              => __( 'Product categories', 'fast-shop' ),
					'singular_name'     => __( 'Product category', 'fast-shop' ),
					'search_items'      => __( 'Product categories', 'fast-shop' ),
					'all_items'         => __( 'Product categories', 'fast-shop' ),
					'parent_item'       => __( 'Product categories', 'fast-shop' ),
					'parent_item_colon' => __( 'Product categories', 'fast-shop' ),
					'edit_item'         => __( 'Category editing', 'fast-shop' ),
					'update_item'       => __( 'Product categories', 'fast-shop' ),
					'add_new_item'      => __( 'Add category', 'fast-shop' ),
					'new_item_name'     => __( 'Product categories', 'fast-shop' ),
					'menu_name'         => __( 'Product categories', 'fast-shop' ),
				),
				'metabox'            => true,
				'hierarchical'       => true,
				"public"             => true,
				"show_ui"            => true,
				"publicly_queryable" => true,
				'show_admin_column'  => true,
			)
		,
			'fs-payment-methods'  => array(
				'object_type'        => 'product',
				'label'              => __( 'Payment methods', 'fast-shop' ),
				'labels'             => array(
					'name'          => __( 'Payment methods', 'fast-shop' ),
					'singular_name' => __( 'Payment method', 'fast-shop' ),
					'add_new_item'  => __( 'Add a payment method', 'fast-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => false,
				"show_ui"            => true,
				"publicly_queryable" => false,
				'meta_box_cb'        => false,
				'metabox'            => false,
				'show_admin_column'  => false,

			),
			'fs-delivery-methods' => array(
				'object_type'        => 'product',
				'label'              => __( 'Delivery methods', 'fast-shop' ),
				'labels'             => array(
					'name'          => __( 'Delivery methods', 'fast-shop' ),
					'singular_name' => __( 'Delivery method', 'fast-shop' ),
					'add_new_item'  => __( 'Add a delivery method', 'fast-shop' ),
				),
//					исключаем категории из лицевой части
				"public"             => false,
				"show_ui"            => true,
				"publicly_queryable" => false,
				'metabox'            => false,
				'meta_box_cb'        => false,
				'show_admin_column'  => false,
				'show_in_quick_edit' => false
			),
			'product-attributes'  => array(
				'object_type'        => 'product',
				'label'              => __( 'Product attributes', 'fast-shop' ),
				'labels'             => array(
					'name'          => __( 'Product attributes', 'fast-shop' ),
					'singular_name' => __( 'Product attributes', 'fast-shop' ),
					'add_new_item'  => __( 'Add property / group of properties', 'fast-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => true,
				"show_ui"            => true,
				"publicly_queryable" => true,

				'metabox'            => null,
				'show_admin_column'  => true,
				'hierarchical'       => true,
				'show_in_quick_edit' => false
			),
			'fs-currencies'       => array(
				'object_type'        => 'product',
				'label'              => __( 'Currencies', 'fast-shop' ),
				'labels'             => array(
					'name'          => __( 'Currencies', 'fast-shop' ),
					'singular_name' => __( 'Currency', 'fast-shop' ),
					'add_new_item'  => __( 'Add Currency', 'fast-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => false,
				"show_ui"            => true,
				"publicly_queryable" => false,
				'metabox'            => false,
				'show_admin_column'  => false,
				'hierarchical'       => false,
				'meta_box_cb'        => false,
				'show_in_quick_edit' => false
			),
			'order-statuses'      => array(
				'object_type'        => 'orders',
				'label'              => __( 'Order statuses', 'fast-shop' ),
				'labels'             => array(
					'name'          => __( 'Order statuses', 'fast-shop' ),
					'singular_name' => __( 'Order statuses', 'fast-shop' ),
					'add_new_item'  => __( 'Add status', 'fast-shop' ),
				),
				//					исключаем категории из лицевой части
				"public"             => false,
				"show_ui"            => true,
				"publicly_queryable" => false,
				'metabox'            => false,
				'show_admin_column'  => true,
				'hierarchical'       => true
			)
		);

		$taxonomies=apply_filters( 'fs_taxonomies', $taxonomies );

		return $taxonomies;
	}

	/**
	 * Создаёт такономии
	 * категории товаров
	 * методы оплаты
	 * методы доставки
	 * свойства товаров
	 * валюты
	 * статусы заказов
	 */
	function create_taxonomy() {
		// сам процесс регистрации таксономий
		if ( $this->shop_taxonomies() ) {
			foreach ( $this->shop_taxonomies() as $key => $taxonomy ) {
				$object_type=$taxonomy['object_type'];
				unset( $taxonomy['object_type'] );
				register_taxonomy( $key, $object_type, $taxonomy );
			}
		}


		// дополнительные поля таксономий, поля отображаются при добавлении или редактировании таксономии

		// поля таксономии харакеристик товара
		add_action( "product-attributes_edit_form_fields", array( $this, 'edit_product_attr_fields' ) );
		add_action( "product-attributes_add_form_fields", array( $this, 'add_product_attr_fields' ) );
		add_action( "create_product-attributes", array( $this, 'save_custom_taxonomy_meta' ) );
		add_action( "edited_product-attributes", array( $this, 'save_custom_taxonomy_meta' ) );

		// поля таксономии валют
		add_action( "fs-currencies_edit_form_fields", array( $this, 'edit_fs_currencies_fields' ) );
		add_action( "fs-currencies_add_form_fields", array( $this, 'add_fs_currencies_fields' ) );
		add_action( "create_fs-currencies", array( $this, 'save_custom_taxonomy_meta' ) );
		add_action( "edited_fs-currencies", array( $this, 'save_custom_taxonomy_meta' ) );

	}

	function edit_product_attr_fields( $term ) {
		$att_type     =get_term_meta( $term->term_id, 'fs_att_type', 1 );
		$display_color=$att_type == 'color' ? 'style="display:table-row"' : '';
		$display_image=$att_type == 'image' ? 'style="display:table-row"' : '';
		echo '<tr class="form-field term-parent-wrap">
        <th scope="row"><label for="fs_att_type">Тип атрибута</label></th>
        <td>
            <select name="fast-shop[fs_att_type]" id="fs_att_type" class="postform">
                <option value="text" ' . selected( 'text', $att_type, 0 ) . '>текст</option>
                <option value="color" ' . selected( 'color', $att_type, 0 ) . '>цвет</option>
                <option value="image" ' . selected( 'image', $att_type, 0 ) . '>изображение</option>
            </select>
            <p class="description">Товары могут иметь разные свойства. Здесь вы можете выбрать какой тип свойства нужен.</p>
        </td>
    </tr>';
		echo '<tr class="form-field term-parent-wrap fs-att-values" ' . $display_color . ' id="fs-att-color">
    <th scope="row"><label>Значение цвета</label></th>
    <td>

       <input type="text"  name="fast-shop[fs_att_color_value]" value="' . get_term_meta( $term->term_id, 'fs_att_color_value', 1 ) . '" class="fs-color-select">
   </td>
</tr>';
		$atach_image_id=get_term_meta( $term->term_id, 'fs_att_image_value', 1 );
		$att_image     =$atach_image_id ? wp_get_attachment_url( $atach_image_id, 'medium' ) : '';
		$display_button=! empty( $att_image ) ? 'block' : 'none';
		$display_text  =! empty( $att_image ) ? 'изменить изображение' : 'выбрать изображение';
		if ( ! empty( $att_image ) ) {
			$class="show";
		} else {
			$class="hidden";
		}
		echo '<tr class="form-field term-parent-wrap fs-att-values" ' . $display_image . ' id="fs-att-image">
			  <th scope="row"><label>Изображение</label></th><td><div class="fs-fields-container">';
		echo '<div class="fs-selected-image ' . $class . '" style=" background-image: url(' . $att_image . ');"></div>';
		echo '<button type="button" class="select_file">' . $display_text . '</button>
              <input type="hidden"  name="fast-shop[fs_att_image_value]" value="' . get_term_meta( $term->term_id, 'fs_att_image_value', 1 ) . '" class="fs-image-select">
              <button type="button" class="delete_file" style="display:' . $display_button . '"> удалить изображение </button></div></td></tr> ';
	}


	function add_product_attr_fields( $term ) {

		$display_color='style="display:none"';
		$display_image='style="display:none"';
		echo '<div class="form-field term-parent-wrap">
    <label for="fs_att_type"> Тип атрибута </label>

    <select name="fast-shop[fs_att_type]" id="fs_att_type" class="postform">
        <option value="text"> текст</option>
        <option value="color"> цвет</option>
        <option value="image"> изображение</option>
    </select>
    <p class="description"> Товары могут иметь разные свойства . Здесь вы можете выбрать какой тип свойства нужен .</p>

</div> ';
		echo '<div class="form-field term-parent-wrap fs-att-values" ' . $display_color . ' id="fs-att-color">
<label> Значение цвета </label>


<input type="text"  name="fast-shop[fs_att_color_value]" value="" class="fs-color-select">

</div> ';

		$display_button="'block':'none'";
		$display_text  ='выбрать изображение';
		echo '<div class="form-field term-parent-wrap  fs-att-values" ' . $display_image . ' id="fs-att-image">
<label> Изображение</label>
<div class="fs-fields-container">
   <div class="fs-selected-image" style=" background-image: url();"></div>

   <button type="button" class="select_file"> ' . $display_text . '</button>
   <input type="hidden"  name="fast-shop[fs_att_image_value]" value="" class="fs-image-select">
   <button type="button" class="delete_file" style="display:' . $display_button . '"> удалить изображение </button>	
</div>


</div> ';
	}

	function add_fs_currencies_fields() {
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Currency code', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[currency-code]" id="currency-code" type="text" value="" size="5">
			<p class="description"> ' . __( 'International Currency Symbol', 'fast-shop' ) . ' </p></td>
		</tr> ';

		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Cost in basic currency', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[cost-basic]" id="currency-code" type="text" value="" size="5"> 
			<p class="description"> ' . __( 'Only digits with a dot are allowed', 'fast-shop' ) . ' </p></td>
		</tr> ';
	}

	function edit_fs_currencies_fields( $term ) {
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Currency code', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[currency-code]" id="currency-code" type="text" value="' . get_term_meta( $term->term_id, 'currency-code', 1 ) . '" size="5">
			<p class="description"> ' . __( 'International Currency Symbol', 'fast-shop' ) . ' </p></td>
		</tr> ';


		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Cost in basic currency', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[cost-basic]" id="currency-code" type="text" value="' . get_term_meta( $term->term_id, 'cost-basic', 1 ) . '" size="5">
			<p class="description"> ' . __( 'Only digits with a dot are allowed', 'fast-shop' ) . ' </p></td>
		</tr> ';
	}


	/**
	 * метод срабатывает в момент сохранения доп. полей таксономии
	 *
	 * @param $term_id
	 */
	function save_custom_taxonomy_meta( $term_id ) {
		if ( ! isset( $_POST['fast-shop'] ) ) {
			return;
		}
		$extra=array_map( 'trim', $_POST['fast-shop'] );
		foreach ( $extra as $key => $value ) {
			if ( empty( $value ) ) {
				delete_term_meta( $term_id, $key ); // удаляем поле если значение пустое
				continue;
			}
			update_term_meta( $term_id, $key, $value ); // add_term_meta() работает автоматически
		}

		return $term_id;
	}

//Получаем списком все категории продуктов
	public function get_product_terms( $terms='catalog' ) {
		$args   =array(
			'orderby'                => 'id',
			'order'                  => 'ASC',
			'hide_empty'             => false,
			'exclude'                => array(),
			'exclude_tree'           => array(),
			'include '               => array(),
			'number'                 => '',
			'fields'                 => 'all',
			'slug'                   => '',
			'parent'                 => 0,
			'hierarchical'           => true,
			'child_of'               => 0,
			'get'                    => '',
			'name__like'             => '',
			'pad_counts'             => false,
			'offset'                 => '',
			'search'                 => '',
			'cache_domain'           => 'core',
			'name'                   => '',
			'childless'              => false,
			'update_term_meta_cache' => true,
			'meta_query'             => '',
		);
		$myterms=get_terms( array( $terms ), $args );
		if ( $myterms ) {
			echo "<ul>";
			foreach ( $myterms as $term ) {
				$link =get_term_link( $term->term_id );
				$class="";
				if ( strripos( $link, $_SERVER['REQUEST_URI'] ) ) {
					$class='class="active"';
				}
				echo "<li $class><a href=\"$link\">$term->name</a></li>";
			}
			echo "</ul>";

		}

	}

	/**
	 * удаляет все категории товаров
	 */
	public function delete_product_categories() {
		global $fs_config;
		$terms=get_terms( array(
				'taxonomy'   => $fs_config->data['product_taxonomy'],
				'hide_empty' => false
			)
		);
		if ( $terms ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $fs_config->data['product_taxonomy'] );
			}
		}

	}

	/**
	 * удаляет все свойства товаров
	 */
	public function delete_product_attributes() {
		global $fs_config;
		$terms=get_terms( array(
				'taxonomy'   => $fs_config->data['product_att_taxonomy'],
				'hide_empty' => false
			)
		);
		if ( $terms ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, $fs_config->data['product_att_taxonomy'] );
			}
		}

	}


	/**
	 * Регистриует колонку код валюты в таксономии валют
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	function add_fs_currencies_columns( $columns ) {
		$columns['сurrency-code']=__( 'Currency code', 'fast-shop' );
		$columns['cost-basic']   =__( 'Cost', 'fast-shop' );
		unset( $columns['description'], $columns['posts'] );

		return $columns;
	}

	function fs_currencies_column_content( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'сurrency-code':
				//do your stuff here with $term or $term_id
				$content=get_term_meta( $term_id, 'currency-code', 1 );
				break;
			case 'cost-basic':
				//do your stuff here with $term or $term_id
				$content=get_term_meta( $term_id, 'cost-basic', 1 );
				break;
			default:
				break;
		}

		return $content;
	}

}