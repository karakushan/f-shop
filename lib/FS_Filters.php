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

        if (!empty($url['sort_custom'])){

            switch ($url['sort_custom']){
                case 'price_asc':
                    $query->set('meta_key','fs_price');
                    $query->set('orderby','meta_value_num');
                    $query->set('order','ASC');

                    break;
            }

        }

        //Фильтрируем по к-во выводимых постов на странице
        if (isset($url['posts_per_page'])){
            $per_page=(int)$url['posts_per_page'];
            $_SESSION['fs_user_settings']['posts_per_page']=$per_page;
            $query->set('posts_per_page',$per_page);
        }

        //Фильтруем по свойствам (атрибутам)
        if (isset($url['attr'])){
            global $wpdb;
            $escl_p=array();
            $q=get_queried_object();
            $excludeposts = $wpdb->get_results( "SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id='$q->term_id'" );
            if ($excludeposts)
                foreach ( $excludeposts as $posts) {
                    $post_id=$posts->object_id;
                    if ($url['attr'])
                        foreach ($url['attr'] as $key=>$attr) {
                            foreach ($attr as $att_key=>$att) {
                                if (get_post_meta($post_id, $this->conf->meta['attributes'],false)!=false){
                                    $post_meta=get_post_meta($post_id,$this->conf->meta['attributes'],false);
                                    $post_meta=$post_meta[0];
                                    if (isset($post_meta[$key][$att])){
                                        if ($post_meta[$key][$att]==1){
                                            $escl_p[]=$post_id;
                                        }
                                    }

                                }
                            }
                        }
                }

            if (count($escl_p )) $escl_p=array_unique($escl_p);
            $query->set('post__in',$escl_p);
        }

        return $query;
    }//end filter_curr_product()

    public function range_slider()
    {
        $slider='
		<div class="range">
			<div id="slider-range" data-uri="0-0"></div>
			<div id="amount_show" class="ashow"><span>0</span> грн - <span>2500</span> грн</div>
		</div>
		';
        return $slider;
    }//end range_slider()

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