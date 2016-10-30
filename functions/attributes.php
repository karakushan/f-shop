<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 08.10.2016
 * Time: 17:03
 */

/**
 * получает все зарегистрированные группы атрибутов или свойтва товаров
 * @return array|mixed|void
 */
function fs_get_attributes_group(){
    $group=get_option('fs-attr-groups')!=false?get_option('fs-attr-groups'):array();
    return $group;
}

/**
 * получает все зарегистрированные атрибуты или свойтва товаров
 * @return array|mixed|void
 */
function fs_get_attributes(){
    $attributes=get_option('fs-attributes')!=false?get_option('fs-attributes'):array();
    return $attributes;
}

/**
 * Получает термины постов выводимых на текущей странице, нужно указать айди родительского термина
 * @param $parent_term_id
 * @return массив объектов поста
 */
function fs_current_screen_attributes($parent_term_id){
    global $wp_query;
    $posts=new WP_Query(array(
        'posts_per_page'=>-1,
        'fields'=>'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'catalog',
                'field'    => 'id',
                'terms'    => $wp_query->queried_object_id
            )
        )
    ));
    $ids=$posts->posts;
    $obj_terms=array();
    $terms=wp_get_object_terms($ids, 'product-attributes');
    foreach ($terms as $key => $term) {
        if($term->parent!=$parent_term_id) continue;
        $obj_terms[]=$term;
    }
    wp_reset_postdata();
    return $obj_terms;
}

