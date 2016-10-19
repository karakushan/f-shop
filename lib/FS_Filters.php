<?php
namespace FS;
/**
 * Class FS_Filters
 * @package FS
 */
class FS_Filters
{
    protected $conf;
    private  $exclude=array(
        'fs_filter',
        'price_start',
        'price_end',
        'sort_custom'
        );
    function __construct()
    {

        $this->conf=new FS_Config();
        add_action('pre_get_posts',array($this,'filter_curr_product'));
        add_action('pre_get_posts',array($this,'filter_by_query'));
        add_shortcode( 'fs_range_slider', array($this,'range_slider'));

    }

    /**
     * @param $query
     */
    public function filter_curr_product($query)
    {
        $config = new FS_Config;
        $validate_url=filter_var($_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL);

        if (!$validate_url && !isset($_REQUEST['fs_filter'])) return;

        if (!$query->is_main_query()) return;

        $arr_url=urldecode($_SERVER['QUERY_STRING']);
        parse_str ($arr_url,$url);

        //Фильтрируем по значениям диапазона цен
        if (isset($url['price_start']) && isset($url['price_end'])) {
            $price_start=!empty($url['price_start']) ? (int)$url['price_start'] : 0;
            $price_end=!empty($url['price_end']) ? (int)$url['price_end'] : 99999999999999999;

            $query->set('post_type','product');
            $query->set('meta_query',array(
                array(
                    'key'     => $this->conf->meta['price'],
                    'value'   => array( $price_start,$price_end),
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC',
                    )
                )

            );
            $query->set('orderby','meta_value_num');
        }



        //Фильтрируем по к-во выводимых постов на странице
        if (isset($url['posts_per_page'])){
            $per_page=(int)$url['posts_per_page'];
            $_SESSION['fs_user_settings']['posts_per_page']=$per_page;
            $query->set('posts_per_page',$per_page);
        }

        //Фильтрируем по возрастанию и падению цены
        if (isset($url['order_type'])){
            //сортируем по цене в возрастающем порядке
            if ($url['order_type']=='price_asc'){
                $query->set('meta_query',array(
                    'price'=>array(
                        'key'     => $this->conf->meta['price'],
                        'compare' => 'EXISTS',
                        'type'    => 'NUMERIC',
                        )
                    )

                );
                $query->set('orderby','price');
                $query->set( 'order' , 'ASC');
            }
            //сортируем по цене в спадающем порядке
            if ($url['order_type']=='price_desc'){
                $query->set('meta_query',array(
                    'price'=>array(
                        'key'     => $this->conf->meta['price'],
                        'compare' => 'EXISTS',
                        'type'    => 'NUMERIC',
                        )
                    )

                );
                $query->set('orderby','price');
                $query->set( 'order' , 'DESC');
            }
            //сортируем по названию по алфавиту
            if ($url['order_type']=='name_asc'){
                $query->set('orderby','title');
                $query->set( 'order' , 'ASC');
            }
            //сортируем по названию по алфавиту в обратном порядке
            if ($url['order_type']=='name_desc'){
                $query->set('orderby','title');
                $query->set( 'order' , 'DESC');
            }
            if ($url['order_type']=='field_action'){
                $query->set('meta_query',array(
                  array(
                    'key'     => $this->conf->meta['action'],
                    'compare' => 'EXISTS',
                    )
                  )
                );
            }

        }

        //Фильтруем по свойствам (атрибутам)
        if (!empty($url['attr'])){
          /*  echo "<pre>";
            print_r($query->query);
            echo "</pre>";*/
            global $wpdb;
            $taxonomy=$query->query['catalog'];
            $meta_key=$config->meta['attributes'];
            $exclude_posts=array();
            $include_posts=array();

            //  преобразовываем в массив сроку запроса
            $build_array=array();
            foreach ($url['attr'] as $key => $attr) {
                $http_vars=explode('|',$attr);
                $http_vars=array_diff($http_vars, array(''));
                $build_array[$key]=$http_vars;
            }
         
            //получаем все посты категории
            $posts = $wpdb->get_results("SELECT t1.term_id, t2.object_id  FROM $wpdb->terms AS t1, $wpdb->term_relationships AS t2 WHERE t1.slug='$taxonomy' AND t2.term_taxonomy_id=t1.term_id");
            if (!is_null($posts)) {
              foreach ($posts as $key => $post) {
                $curent = $wpdb->get_row("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '$post->object_id' AND meta_key='$meta_key'");
                
                $meta_value=!is_null($curent)?unserialize($curent->meta_value):'';

                // перебираем значения масива http запроса и ищем наличие значения в массиве мета-полей 
                if ($curent!=false || empty($meta_value)) {
                 foreach ($build_array as $key => $builds) {
                    if (!isset($meta_value[$key])){
                        $exclude_posts[]=$post->object_id;
                        continue;
                    }else{
                       foreach ($builds as $key2 => $build) {
                        if (in_array($build,$meta_value[$key])) {
                            $include_posts[]=$post->object_id; 
                        }else{
                            $exclude_posts[]=$post->object_id;
                        }
                    }
                }

            }
        }else{
            $exclude_posts[]=$post->object_id;
        }

    }
}

if ($include_posts) {
    $query->set('post__in',$include_posts);
} else {
    $query->set('post__not_in',$exclude_posts);
}



}
return $query;
    }//end filter_curr_product()



    /**
     * @param $group
     * @param string $type
     * @param string $option_default
     */
    public function attr_group_filter($group, $type='option', $option_default='Выберите значение')
    {

        $fs_atributes=get_option('fs-attr-group');
        /*        echo "<pre>";
                print_r($fs_atributes);
                echo "</pre>";*/
                if (!isset($fs_atributes[$group]['attributes'])) return;

                $arr_url=urldecode($_SERVER['QUERY_STRING']);
                parse_str ($arr_url,$url);

                if ( $type=='option') {
                    echo '<select name="'.$group.'" data-fs-action="filter"><option value="">'.$option_default.'</option>';
                    foreach ($fs_atributes[$group]['attributes'] as $key => $value) {
                        $redirect_url=esc_url(add_query_arg(array('fs_filter'=>1,'attr['.$group.'][]'=>$key),urldecode($_SERVER['REQUEST_URI'])));
                        if (isset($url['attr'][$group])){
                            $selected=selected($key,$url['attr'][$group],false);
                        }else{
                            $selected="";
                        }
                        echo '<option value="'.$redirect_url.'" '.$selected.'>'.$value.'</option>';
                    }
                    echo '</select>';
                }
                if ($type=='list') {
                    echo '<ul>';
                    foreach ($fs_atributes[$group]['attributes'] as $key => $value) {
                        $redirect_url=esc_url(add_query_arg(array('fs_filter'=>1,'attr['.$group.'][]'=>$key),urldecode($_SERVER['REQUEST_URI'])));
                        $class=(isset($url['attr'][$group]) && $key==$url['attr'][$group]?'class="active"':"");
                        echo '<li '.$class.'><a href="'.$redirect_url.'" data-fs-action="filter" >'.$value.'</a></li>';
                    }
                    echo '</ul>';
                }
    }//end attr_group_filter()

    /**
     * метод позволяет вывести поле типа select  для изменения к-ва выводимых постов на странице
     * @param  [array] $post_count массив к-ва выводимых записей например array(10,20,30,40)
     * @return [type]             html код селекта с опциями
     */
    public function posts_per_page_filter($post_count)
    {
        $req=isset($_SESSION['fs_user_settings']['posts_per_page']) ? $_SESSION['fs_user_settings']['posts_per_page'] : get_option("posts_per_page");

        if(count($post_count)){
            $filter = '<select name="post_count" onchange="document.location=this.options[this.selectedIndex].value">';
            foreach ($post_count as $key => $count) {
                $filter.= '<option value="'.add_query_arg(array("fs_filter"=>1,"posts_per_page"=>$count)).'" '.selected($count,$req,false).'>'.$count.'</option>';

            }
            $filter.= '</select>';

        }else{
            $filter = false;

        }
        return $filter;

    }

    /**
     * фильтрирует посты методом pre_get_posts, берёт данные из адресной строки,
     * сработает только при наличии параметра fs_filter
     * @param $query
     */
    public function filter_by_query($query){
        if (!isset($_REQUEST['fs_filter'])) return;
        if (!$query->is_main_query()) return;
        $query_string=array();

        if (!empty($_GET)){
            foreach ($_GET as $key=>$item) {
                if (in_array($key,$this->exclude)) continue;
                $query_string[$key]=filter_input(INPUT_GET,$key,FILTER_SANITIZE_STRING);
            }
        }

        if (count($query_string)){
            foreach ($query_string as $query_key=>$query_value) {
                $query->set($query_key,$query_value);
            }
        }

    }
}