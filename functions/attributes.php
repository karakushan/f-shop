<?php
/**
 * Возвращает массив атрибутов конкретного товара
 * @return array массив атрибутов
 */
function fs_get_attributes_group(int $product_id=0){
    global $post;
    $product_id=$product_id==0 ? $post->ID : $product_id;
    $terms=wp_get_object_terms($product_id, 'product-attributes');
    $parents=array();
    foreach ($terms as $key =>$term) {
        $attr_type=get_term_meta($term->term_id,'fs_att_type',1);
        $attr_type=empty($attr_type) ? 'text' : $attr_type; 
        if ($attr_type=='text') {
            $attr_value=$term->name;
        } else {
            $attr_value=get_term_meta($term->term_id,'fs_att_'.$attr_type.'_value',1);
        }
        $parents[$term->parent][$term->term_id]=array('name'=>$term->name,'type'=>$attr_type,'value'=>$attr_value);
    }
    
    return $parents;
}

/**
* получает заданное свойство товара с вложенными свойтвами
* @return array
*/
function fs_get_attribute(int $attr_id,int $product_id=0,$args=array()){
    $args=wp_parse_args($args,array('return'=>1));
    $attributes=fs_get_attributes_group($product_id);

    if (isset($attributes[$attr_id])) {
        if ($args['return']) {
           $first_attr=array_shift($attributes[$attr_id]);
           $atts=$first_attr['value'];
       }else{
         $atts=array(
            'name'=>get_term_field('name',$attr_id),
            'children'=>$attributes[$attr_id]
            ); 
     }
 }else{
    if($args['return']){
        $atts='-';
    }else{
        $atts=array(
            'name'=>get_term_field('name',$attr_id),
            'children'=>array()
            ); 
    }

}

return $atts;
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

// select фильтр сортировки по таксономии
function fs_taxonomy_select_filter($taxonomy='catalog',$first_option='сделайте выбор'){
    $manufacturers=get_terms(array('taxonomy'=>$taxonomy,'hide_empty'=>false));
    $filter='';
    if ($manufacturers) {
     $filter.='<select name="tax-'.$taxonomy.'" data-fs-action="filter">';
     $filter.='<option value="'.remove_query_arg(array('tax-'.$taxonomy)).'">'.$first_option.'</option>';
     foreach ($manufacturers as $key => $manufacturer) {
        if(isset($_GET['tax-'.$taxonomy])){
            $selected=selected($manufacturer->term_id,$_GET['tax-'.$taxonomy],0);
        }else{
         $selected='';
     }
     $filter.='<option value="'.add_query_arg(array('fs_filter'=>wp_create_nonce('fast-shop'),'tax-'.$taxonomy=>$manufacturer->term_id)).'" '.$selected.'>'.$manufacturer->name.'</option>';
 }
 $filter.='</select>';
}
return $filter;
}

// select фильтр сортировки по разным параметрам
function fs_types_sort_filter($first_option='сделайте выбор'){
    $filter='';
    $order_types=array(
        'price_asc'=>array(
            'name'=>'цена по возрастанию'
            ),
        'price_desc'=>array(
            'name'=>'цена по убыванию'
            ),
        'name_asc'=>array(
            'name'=>'по алфавиту'
            ),
        'name_desc'=>array(
            'name'=>'по алфавиту в обратном порядке'
            ), 
        'date_desc'=>array(
            'name'=>'последние опубликованные'
            ), 
        'date_asc'=>array(
            'name'=>'давнее опубликованные'
            )

        );

    if ( $order_types) {
        $filter.='<select name="order_type" data-fs-action="filter">';
        $filter.='<option value="'.remove_query_arg(array('order_type')).'">'.$first_option.'</option>';
        foreach ($order_types as $key =>  $order_type) {
          if(isset($_GET['order_type'])){
            $selected=selected( $key,$_GET['order_type'],0);
        }else{
         $selected='';
     }
     $filter.='<option value="'.add_query_arg(array('fs_filter'=>wp_create_nonce('fast-shop'),'order_type'=>$key)).'" '.$selected.'>'.$order_type['name'].'</option>';
 }
 $filter.='</select>';
}

return $filter;
}

// селект фильтр для фильтрования товаров по наличию
function fs_aviable_select_filter($first_option='сделайте выбор'){
    $filter='';
    $aviable_types=array(
        'aviable'=>array('name'=>__('in stock','fast-shop')),
        'not_available'=>array('name'=>__('not available','fast-shop')),
        );
    if ( $aviable_types) {
        $filter.='<select name="order_type" data-fs-action="filter">';
        $filter.='<option value="'.remove_query_arg(array('aviable')).'">'.$first_option.'</option>';
        foreach ($aviable_types as $key =>  $order_type) {
          if(isset($_GET['aviable'])){
            $selected=selected( $key,$_GET['aviable'],0);
        }else{
         $selected='';
     }
     $filter.='<option value="'.add_query_arg(array('fs_filter'=>wp_create_nonce('fast-shop'),'aviable'=>$key)).'" '.$selected.'>'.$order_type['name'].'</option>';
 }
 $filter.='</select>';
}
return $filter;
}

