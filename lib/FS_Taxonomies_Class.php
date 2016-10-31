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
                'add_new_item'=>'Добавить'
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

            add_action("product-attributes_edit_form_fields",array($this,'edit_product_attr_fields') );
            add_action("product-attributes_add_form_fields",array($this,'add_product_attr_fields') );
            add_action("create_product-attributes", array($this,'save_custom_taxonomy_meta'));
            add_action("edited_product-attributes", array($this,'save_custom_taxonomy_meta'));

        }

        function  edit_product_attr_fields($term){
            $att_type=get_term_meta($term->term_id,'fs_att_type',1);
            $display_color= $att_type=='color'?'style="display:table-row"':'';
            $display_image= $att_type=='image'?'style="display:table-row"':'';
            echo '<tr class="form-field term-parent-wrap" >
			<th scope="row"><label for="fs_att_type">Тип атрибута</label></th>
			<td>
				<select name="fast-shop[fs_att_type]" id="fs_att_type" class="postform">
				<option value="text" '.selected('text', $att_type,0).'>текст</option>
				<option value="color" '.selected('color', $att_type,0).'>цвет</option>
				<option value="image" '.selected('image', $att_type,0).'>изображение</option>
                </select>
                <p class="description">Товары могут иметь разные свойства. Здесь вы можете выбрать какой тип свойства нужен.</p>
							</td>
		</tr>';
            echo '<tr class="form-field term-parent-wrap fs-att-values" '.$display_color.' id="fs-att-color">
			<th scope="row"><label>Значение цвета</label></th>
			<td>
	
               <input type="text"  name="fast-shop[fs_att_color_value]" value="'.get_term_meta($term->term_id,'fs_att_color_value',1).'" class="fs-color-select">
							</td>
		</tr>';
            $att_image=get_term_meta($term->term_id,'fs_att_image_value',1)!='' ? wp_get_attachment_url(get_term_meta($term->term_id,'fs_att_image_value',1)) : '';
            $display_button=!empty($att_image)?'block':'none';
            $display_text=!empty($att_image)?'изменить изображение':'выбрать изображение';
            echo '<tr class="form-field term-parent-wrap fs-att-values" '.$display_image.' id="fs-att-image">
			<th scope="row"><label>Изображение</label></th>
			<td>
			<div class="fs-fields-container">
			<div class="fs-selected-image" style=" background-image: url('.$att_image.');"></div>
			
				<button type="button" class="select_file">'.$display_text.'</button>
               <input type="hidden"  name="fast-shop[fs_att_image_value]" value="'.get_term_meta($term->term_id,'fs_att_image_value',1).'" class="fs-image-select">
						<button type="button" class="delete_file" style="display:'. $display_button.'">удалить изображение</button>	
							</div>
							</td>
							
		</tr>';
        }


        function  add_product_attr_fields($term){
            $att_type=get_term_meta($term->term_id,'fs_att_type',1);
            $display_color= $att_type=='color'?'style="display:block"':'';
            $display_image= $att_type=='image'?'style="display:block"':'';
            echo '<div class="form-field term-parent-wrap" >
			<label for="fs_att_type">Тип атрибута</label>
			
				<select name="fast-shop[fs_att_type]" id="fs_att_type" class="postform">
				<option value="text" '.selected('text', $att_type,0).'>текст</option>
				<option value="color" '.selected('color', $att_type,0).'>цвет</option>
				<option value="image" '.selected('image', $att_type,0).'>изображение</option>
                </select>
                <p class="description">Товары могут иметь разные свойства. Здесь вы можете выбрать какой тип свойства нужен.</p>
							
		</div>';
            echo '<div class="form-field term-parent-wrap fs-att-values" '.$display_color.' id="fs-att-color">
			<label>Значение цвета </label>
			
	
               <input type="text"  name="fast-shop[fs_att_color_value]" value="'.get_term_meta($term->term_id,'fs_att_color_value',1).'" class="fs-color-select">
							
		</div>';
            $att_image=get_term_meta($term->term_id,'fs_att_image_value',1)!='' ? wp_get_attachment_url(get_term_meta($term->term_id,'fs_att_image_value',1)) : '';
            $display_button=!empty($att_image)?'block':'none';
            $display_text=!empty($att_image)?'изменить изображение':'выбрать изображение';
            echo '<div class="form-field term-parent-wrap  fs-att-values" '.$display_image.' id="fs-att-image">
			<label>Изображение</label>
			<div class="fs-fields-container">
			<div class="fs-selected-image" style=" background-image: url('.$att_image.');"></div>
			
				<button type="button" class="select_file">'.$display_text.'</button>
               <input type="hidden"  name="fast-shop[fs_att_image_value]" value="'.get_term_meta($term->term_id,'fs_att_image_value',1).'" class="fs-image-select">
						<button type="button" class="delete_file" style="display:'. $display_button.'">удалить изображение</button>	
							</div>
							
							
		</div>';
        }

        function save_custom_taxonomy_meta( $term_id ) {
            if ( ! isset($_POST['fast-shop']) )
                return;
            $extra = array_map('trim', $_POST['fast-shop']);
            foreach( $extra as $key => $value ){
                if( empty($value) ){
                    delete_term_meta( $term_id, $key ); // удаляем поле если значение пустое
                    continue;
                }
                update_term_meta( $term_id, $key, $value ); // add_term_meta() работает автоматически
            }
            return $term_id;
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