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

