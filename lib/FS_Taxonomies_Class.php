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

		$this->config = new FS_Config();

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
		$taxonomies = array(
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
				'show_admin_column'  => false
			),
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
			)
		);
		if ( fs_option( 'discounts_on' ) == 1 ) {
			$taxonomies['fs-discounts'] = array(
				'object_type'        => 'product',
				'label'              => __( 'Discounts', 'fast-shop' ),
				'labels'             => array(
					'name'          => __( 'Discounts', 'fast-shop' ),
					'singular_name' => __( 'Discount', 'fast-shop' ),
					'add_new_item'  => __( 'Add Discount', 'fast-shop' ),
					'edit_item'     => 'Edit Discount',
					'update_item'   => 'Update Discount',
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
			);
		}
		if ( fs_option( 'multi_currency_on' ) == 1 ) {
			$taxonomies['fs-currencies'] = array(
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
			);
		}

		$taxonomies = apply_filters( 'fs_taxonomies', $taxonomies );

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
				$object_type = $taxonomy['object_type'];
				unset( $taxonomy['object_type'] );
				register_taxonomy( $key, $object_type, $taxonomy );
			}
		}


		// дополнительные поля таксономий, поля отображаются при добавлении или редактировании таксономии

		// поля таксономии категорий товара
		add_action( "catalog_edit_form_fields", array( $this, 'edit_taxonomy_fields' ), 10, 2 );
		add_action( "catalog_add_form_fields", array( $this, 'add_taxonomy_fields' ), 10, 1 );
		add_action( "create_catalog", array( $this, 'save_taxonomy_fields' ), 10, 2 );
		add_action( "edited_catalog", array( $this, 'save_taxonomy_fields' ), 10, 2 );

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

		// поля таксономии способов оплаты
		add_action( "fs-payment-methods_edit_form_fields", array( $this, 'edit_fs_payment_methods_fields' ) );
		add_action( "fs-payment-methods_add_form_fields", array( $this, 'add_fs_payment_methods_fields' ) );
		add_action( "create_fs-payment-methods", array( $this, 'save_custom_taxonomy_meta' ) );
		add_action( "edited_fs-payment-methods", array( $this, 'save_custom_taxonomy_meta' ) );

		// поля таксономии кидок
		add_action( "fs-discounts_edit_form_fields", array( $this, 'edit_fs_discounts_fields' ) );
//		add_action( "fs-discounts_add_form_fields", array( $this, 'add_fs_discounts_fields' ) );

		add_action( "create_fs-discounts", array( $this, 'save_custom_taxonomy_meta' ) );
		add_action( "edited_fs-discounts", array( $this, 'save_custom_taxonomy_meta' ) );

	}

	/**
	 * Метод выводит мета поля таксономии
	 * массив полей берётся из класа FS_Config
	 * из метода get_taxonomy_fields()
	 * который позволяет устанавливать свои поля серез фильтр fs_taxonomy_fields
	 * TODO: в дальнейшем метаполя всех таксономий выводить с помощью этого метода
	 *
	 * @param $term
	 * @param $taxonomy
	 */
	function edit_taxonomy_fields( $term, $taxonomy ) {
		global $fs_config;
		$form   = new FS_Form_Class();
		$fields = $fs_config->get_taxonomy_fields();
		if ( count( $fields[ $taxonomy ] ) ) {
			foreach ( $fields[ $taxonomy ] as $name => $field ) {
				$field['args']['value'] = get_term_meta( $term->term_id, $name, 1 );
				echo '<tr class="form-field taxonomy-thumbnail-wrap">';
				echo '<th scope="row"><label for="taxonomy-thumbnail">' . esc_attr( $field['name'] ) . '</label></th>';
				echo '<td>';
				$form->render_field( $name, $field['type'], $field['args'] );
				echo '</td>';
				echo '</tr>';
			}
		}
	}

	/**
	 * Сохраняет значение мета - полей при добавлении нового термина
	 *
	 * @param $taxonomy
	 */
	function add_taxonomy_fields( $taxonomy ) {
		global $fs_config;
		$form   = new FS_Form_Class();
		$fields = $fs_config->get_taxonomy_fields();
		if ( count( $fields[ $taxonomy ] ) ) {
			foreach ( $fields[ $taxonomy ] as $name => $field ) {
				$id = str_replace( '_', '-', sanitize_title( 'fs-' . $name . '-' . $field['type'] ) );
				echo '<div class="form-field ' . esc_attr( $name ) . '-wrap">';
				echo '<label for="' . esc_attr( $id ) . '">' . esc_attr( $field['name'] ) . '</label>';
				$form->render_field( $name, $field['type'], $field['args'] );
				echo '</div>';
			}
		}
	}

	/**
	 * Сохраняет все метаполя таксономии
	 * если значение пустое, поле удаляется из БД
	 * TODO: удалить дубликаты этой функции
	 *
	 * @param $term_id
	 */
	function save_taxonomy_fields( $term_id ) {
		global $fs_config;
		$term     = get_term( $term_id );
		$taxonomy = $term->taxonomy;
		$fields   = $fs_config->get_taxonomy_fields();
		if ( count( $fields[ $taxonomy ] ) ) {
			foreach ( $fields[ $taxonomy ] as $name => $field ) {
				if ( isset( $_POST[ $name ] ) && $_POST[ $name ] != '' ) {
					update_term_meta( $term_id, $name, $_POST[ $name ] );
				} else {
					delete_term_meta( $term_id, $name );
				}
			}
		}
	}

	function edit_product_attr_fields( $term ) {

		$att_type   = get_term_meta( $term->term_id, 'fs_att_type', 1 );
		$attr_types = array(
			'text'  => array( 'name' => 'текст' ),
			'color' => array( 'name' => 'цвет' ),
			'image' => array( 'name' => 'изображение' ),
			'range' => array( 'name' => 'диапазон' )
		);
		echo '<tr class="form-field term-parent-wrap">
        <th scope="row"><label for="fs_att_type">Тип атрибута</label></th>
        <td>
            <select name="fast-shop[fs_att_type]" id="fs_att_type" class="postform">';
		if ( ! empty( $attr_types ) ) {
			foreach ( $attr_types as $att_key => $attr_type ) {
				echo '<option value = "' . $att_key . '" ' . selected( $att_key, $att_type, 0 ) . ' > ' . $attr_type['name'] . '</option >';
			}
		}
		echo '</select>
            <p class="description">Товары могут иметь разные свойства. Здесь вы можете выбрать какой тип свойства нужен.</p>
        </td>
    </tr>';
		echo '<tr class="form-field term-parent-wrap  fs-att-values fs-att-color" style="' . ( $att_type == 'color' ? "display:table-row" : "display:none" ) . '" class="fs-att-color">
                <th scope="row"><label>Значение цвета</label></th>
               <td>
               <input type="text"  name="fast-shop[fs_att_color_value]" value="' . get_term_meta( $term->term_id, 'fs_att_color_value', 1 ) . '" class="fs-color-select">
                </td>
			 </tr>';

		echo '<tr class="form-field term-parent-wrap fs-att-values fs-att-range" style="' . ( $att_type == 'range' ? "display:table-row" : "display:none" ) . '">
    <th scope="row"><label>Начало диапазона</label></th>
    <td>
       <input type="number" step="0.01"  name="fast-shop[fs_att_range_start_value]" placeholder="0" value="' . get_term_meta( $term->term_id, 'fs_att_range_start_value', 1 ) . '">
   </td>
</tr>
<tr class="form-field term-parent-wrap fs-att-values fs-att-range" style="' . ( $att_type == 'range' ? "display:table-row" : "display:none" ) . '">
    <th scope="row"><label>Конец диапазона</label></th>
    <td>
       <input type="number" step="0.01"  name="fast-shop[fs_att_range_end_value]" placeholder="∞" value="' . get_term_meta( $term->term_id, 'fs_att_range_end_value', 1 ) . '">
   </td>
</tr>
<tr class="form-field term-parent-wrap fs-att-values fs-att-range" style="' . ( $att_type == 'range' ? "display:table-row" : "display:none" ) . '">
    <th scope="row"><label>Использовать к-во покупаемого товара для сравнения с этим атрибутом</label></th>
    <td>
       <input type="checkbox"  name="fast-shop[fs_att_compare]" ' . checked( 1, get_term_meta( $term->term_id, 'fs_att_compare', 1 ), 0 ) . ' value="1">
   </td>
</tr>';
		$atach_image_id = get_term_meta( $term->term_id, 'fs_att_image_value', 1 );
		$att_image      = $atach_image_id ? wp_get_attachment_url( $atach_image_id, 'medium' ) : '';
		$display_button = ! empty( $att_image ) ? 'block' : 'none';
		$display_text   = ! empty( $att_image ) ? 'изменить изображение' : 'выбрать изображение';
		if ( ! empty( $att_image ) ) {
			$class = "show";
		} else {
			$class = "hidden";
		}
		echo '<tr class="form-field term-parent-wrap fs-att-values fs-att-image" style="' . ( $att_type == 'image' ? "display:table-row" : "display:none" ) . '" id="fs-att-image">
			  <th scope="row"><label>Изображение</label></th><td><div class="fs-fields-container">';
		echo '<div class="fs-selected-image ' . $class . '" style=" background-image: url(' . $att_image . ');"></div>';
		echo '<button type="button" class="select_file">' . $display_text . '</button>
              <input type="hidden"  name="fast-shop[fs_att_image_value]" value="' . get_term_meta( $term->term_id, 'fs_att_image_value', 1 ) . '" class="fs-image-select">
              <button type="button" class="delete_file" style="display:' . $display_button . '"> удалить изображение </button></div></td></tr> ';
	}


	function add_product_attr_fields( $term ) {

		$display_color = 'style="display:none"';
		$display_image = 'style="display:none"';
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

		$display_button = "'block':'none'";
		$display_text   = 'выбрать изображение';
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


	/**
	 * Добавляем свои поля на вкладке добавления валюты
	 */
	function add_fs_currencies_fields() {
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Currency code', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[currency-code]" id="currency-code" type="text" value="" size="5" required>
			<p class="description"> ' . __( 'International Currency Symbol', 'fast-shop' ) . ' </p></td>
		</tr> ';

		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Cost in basic currency', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[cost-basic]" id="currency-code" type="text" value="" size="5"> 
			<p class="description"> ' . __( 'Only digits with a dot are allowed', 'fast-shop' ) . ' </p></td>
		</tr> ';


		// === Отображение валюты на сайте ===
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="fs-currency-display"> ' . __( 'Отображение валюты на сайте', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[fs_currency_display]" id="fs-currency-display" type="text" value="" size="5">
			<p class="description"> ' . __( 'Если не указать будет отображаться код валюты типа USD', 'fast-shop' ) . ' </p></td>
		</tr> ';

		// === Поле выбора языка валюты ===
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="fs-currency-locales"> ' . __( 'Язык валюты', 'fast-shop' ) . ' </label></th>
						<td>';
		if ( $this->config->get_locales() ) {
			echo '<select name="fast-shop[fs_currency_locales]" id="fs-currency-locales">';
			echo '<option value="">' . esc_html__( 'Доступные языки', 'fast-shop' ) . '</option>';
			foreach ( $this->config->get_locales() as $key => $locale ) {
				echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $locale ) . '</option>';
			}
			echo '</select>';
		}
		echo '<p class="description"> ' . __( 'Выберите язык из выпадающего списка', 'fast-shop' ) . ' </p></td>
		</tr> ';
	}

	function edit_fs_currencies_fields( $term ) {
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Currency code', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[currency-code]" id="currency-code" type="text" value="' . get_term_meta( $term->term_id, 'currency-code', 1 ) . '" size="5" required>
			<p class="description"> ' . __( 'International Currency Symbol', 'fast-shop' ) . ' </p></td>
		</tr> ';


		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Cost in basic currency', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[cost-basic]" id="currency-code" type="text" value="' . get_term_meta( $term->term_id, 'cost-basic', 1 ) . '" size="5">
			<p class="description"> ' . __( 'Only digits with a dot are allowed', 'fast-shop' ) . ' </p></td>
		</tr> ';


		// === Отображение валюты на сайте ===
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="slug"> ' . __( 'Отображение валюты на сайте', 'fast-shop' ) . ' </label></th>
						<td><input name="fast-shop[fs_currency_display]" id="currency-code" type="text" value="' . get_term_meta( $term->term_id, 'fs_currency_display', 1 ) . '" size="5">
			<p class="description"> ' . __( 'Если не указать будет отображаться код валюты типа USD', 'fast-shop' ) . ' </p></td>
		</tr> ';


		// === Поле выбора языка валюты ===
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="fs-currency-locales"> ' . __( 'Язык валюты', 'fast-shop' ) . ' </label></th>
						<td>';

		if ( $this->config->get_locales() ) {
			echo '<select name="fast-shop[fs_currency_locales]" id="fs-currency-locales">';
			echo '<option value="">' . esc_html__( 'Доступные языки', 'fast-shop' ) . '</option>';
			foreach ( $this->config->get_locales() as $key => $locale ) {
				echo '<option ' . selected( $key, get_term_meta( $term->term_id, 'fs_currency_locales', 1 ) ) . ' value="' . esc_attr( $key ) . '">' . esc_html( $locale ) . '</option>';
			}
			echo '</select>';
		}
		echo '<p class="description"> ' . __( 'Выберите язык из выпадающего списка', 'fast-shop' ) . ' </p></td>
		</tr> ';
	}

	/**
	 * Создаёт поле "Сообщение покупателю  для оплаты" при редактировании способа оплаты
	 *
	 * @param $term объект термина который редактируется
	 */
	function edit_fs_payment_methods_fields( $term ) {
		echo '<tr class="form-field term-currency-code-wrap">
			<th scope="row"><label for="pay-message"> ' . __( 'Сообщение покупателю  для оплаты', 'fast-shop' ) . ' </label></th>
						<td><textarea name="fast-shop[pay-message]" id="pay-message">' . esc_html( get_term_meta( $term->term_id, 'pay-message', 1 ) ) . '</textarea>
			<p class="description">%pay_url% будет заменено на ссылку для оплаты, %pay_name% на название способа оплаты </p></td>
		</tr> ';

	}


	/**
	 * Создаёт поле "Сообщение покупателю  для оплаты" при редактировании способа оплаты
	 *
	 * @param $term объект термина который редактируется
	 */
	function edit_fs_discounts_fields( $term ) {
		$discount_type = get_term_meta( $term->term_id, 'discount_where_is', 1 );

		echo '<tr class="form-field discount-where-is-wrap">'
		     . '<th scope="row"><label for="discount_where_is"> ' . __( 'Скидка активируется если', 'fast-shop' ) . ' </label></th>'
		     . '<td><select name="fast-shop[discount_where_is]" id="discount_where_is">'
		     . '<option>Выберите вариант</option>'
		     . '<option value="sum" ' . selected( $discount_type, 'sum', 0 ) . '>сумма покупки </option>'
		     . '</select>'
		     . '<p class="description">если условие совпадёт, корзина покупателя будет пересчитана с учётом скидки</p></td>
		</tr> ';

		echo '<tr class="form-field discount-where-wrap">'
		     . '<th scope="row"><label for="discount_where"> ' . __( 'Условие скидки', 'fast-shop' ) . ' </label></th>'
		     . '<td><select name="fast-shop[discount_where]" id="discount_where">'
		     . '<option>Выберите вариант</option>'
		     . '<option value="' . esc_attr( '>=' ) . '" ' . selected( get_term_meta( $term->term_id, 'discount_where', 1 ), '>=', 0 ) . '>больше или равно</option>'
		     . '<option value="' . esc_attr( '>' ) . '" ' . selected( get_term_meta( $term->term_id, 'discount_where', 1 ), '>', 0 ) . '>больше</option>'
		     . '<option value="' . esc_attr( '<' ) . '" ' . selected( get_term_meta( $term->term_id, 'discount_where', 1 ), '<', 0 ) . '>меньше</option>'
		     . '<option value="' . esc_attr( '<=' ) . '" ' . selected( get_term_meta( $term->term_id, 'discount_where', 1 ), '<=', 0 ) . '>меньше или равно</option>'
		     . '</select>'
		     . '</td>
		</tr> ';

		echo '<tr class="form-field discount-value-wrap">'
		     . '<th scope="row"><label for="discount_product"> ' . __( 'Значение условия', 'fast-shop' ) . ' </label></th>'
		     . '<td><input name="fast-shop[discount_value]" id="discount_value" value="' . esc_attr( get_term_meta( $term->term_id, 'discount_value', 1 ) ) . '">'
		     . '<p class="description">' . esc_attr( 'сумма или количество в виде числа при которых скидка будет активирована.' ) . '</p></td> 
		</tr> ';

		echo '<tr class="form-field discount-amount-wrap">'
		     . '<th scope="row"><label for="discount_amount"> ' . __( 'Значение скидки', 'fast-shop' ) . ' </label></th>'
		     . '<td><input name="fast-shop[discount_amount]" id="discount_amount" value="' . esc_attr( get_term_meta( $term->term_id, 'discount_amount', 1 ) ) . '">'
		     . '<p class="description">' . esc_attr( 'что будем отминусовывать от скидки, можно указать в виде "10%" или числа .' ) . '</p></td> 
		</tr> ';

	}

	/**
	 * Создаёт поле "Сообщение покупателю  для оплаты" при добававлении способа оплаты
	 */
	function add_fs_payment_methods_fields() {
		echo '<div class="form-field pay-message-code-wrap">
			<label for="pay-message"> ' . __( 'Сообщение покупателю  для оплаты', 'fast-shop' ) . ' </label>
						<textarea name="fast-shop[pay-message]" id="pay-message" cols="40" rows="5"></textarea>
			<p class="description">%pay_url% будет заменено на ссылку для оплаты, %pay_name% на название способа оплаты </p>
		</div> ';

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
		if ( ! isset( $_POST['fast-shop']['fs_att_compare'] ) ) {
			$_POST['fast-shop']['fs_att_compare'] = "-";
		}
		$extra = array_map( 'trim', $_POST['fast-shop'] );
		foreach ( $extra as $key => $value ) {
			if ( empty( $value ) || $value == "-" ) {
				delete_term_meta( $term_id, $key ); // удаляем поле если значение пустое
				continue;
			}
			update_term_meta( $term_id, $key, $value ); // add_term_meta() работает автоматически
		}

		return $term_id;
	}

//Получаем списком все категории продуктов
	public function get_product_terms( $terms = 'catalog' ) {
		$args    = array(
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
		$myterms = get_terms( array( $terms ), $args );
		if ( $myterms ) {
			echo "<ul>";
			foreach ( $myterms as $term ) {
				$link  = get_term_link( $term->term_id );
				$class = "";
				if ( strripos( $link, $_SERVER['REQUEST_URI'] ) ) {
					$class = 'class="active"';
				}
				echo "<li $class><a href=\"$link\">$term->name</a></li>";
			}
			echo "</ul>";

		}

	}

	/**
	 * удаляет все термины из таксономии $taxonomy
	 * не удаляя при этом самой таксономии
	 *
	 * @param string $taxonomy - название таксономии
	 *
	 * @return bool
	 */

	public static function delete_taxonomy_terms( string $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}
		$terms = get_terms( array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false
			)
		);
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$delete = wp_delete_term( intval( $term->term_id ), $taxonomy );
				if ( is_wp_error( $delete ) ) {
					echo $delete->get_error_message();
					continue;
				}
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
		$columns['сurrency-code'] = __( 'Currency code', 'fast-shop' );
		$columns['cost-basic']    = __( 'Cost', 'fast-shop' );
		unset( $columns['description'], $columns['posts'] );

		return $columns;
	}

	function fs_currencies_column_content( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'сurrency-code':
				//do your stuff here with $term or $term_id
				$content = get_term_meta( $term_id, 'currency-code', 1 );
				break;
			case 'cost-basic':
				//do your stuff here with $term or $term_id
				$content = get_term_meta( $term_id, 'cost-basic', 1 );
				break;
			default:
				break;
		}

		return $content;
	}

}