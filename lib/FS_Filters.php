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

        // фильтр по категориям товаров в админке

        add_action( 'restrict_manage_posts',array($this,'ba_admin_posts_filter_restrict_manage_posts'));

    }

    function ba_admin_posts_filter_restrict_manage_posts()
    {
      global $typenow;
      global $wp_query;
      $get_parr=isset($_GET['catalog']) ? $_GET['catalog'] : '';
      if ($typenow!='product') return;
      $terms=get_terms('catalog',array('hide_empty'=>false));
      $select_name=__( 'Product category', 'fast-shop' );
      if ($terms) {
          echo "<select name=\"catalog\">";
          echo "<option value=\"\">$select_name</option>";
          foreach ($terms as $key => $term) {
            echo "<option value=\"$term->slug\" ".selected($term->slug,$get_parr,0).">$term->name</option>";
        }
        echo "</select>";
    }

}

    /**
     * @param $query
     */
    public function filter_curr_product($query)
    {
        if (
            empty($_REQUEST['fs_filter'])
            || !wp_verify_nonce($_REQUEST['fs_filter'],'fast-shop')
            || !$query->is_main_query()
            ) return;

            $config = new FS_Config;
        $tax_query=array();
        $meta_query=array();
        $orderby=array();
        $order='';
        $per_page=get_option("posts_per_page");

        $arr_url=urldecode($_SERVER['QUERY_STRING']);
        parse_str ($arr_url,$url);

        //Фильтрируем по значениям диапазона цен
        if (isset($url['price_start']) && isset($url['price_end'])) {
            $price_start=!empty($url['price_start']) ? (int)$url['price_start'] : 0;
            $price_end=!empty($url['price_end']) ? (int)$url['price_end'] : 99999999999999999;
            $meta_query['price_interval']=
            array(
                'key'     => $this->conf->meta['price'],
                'value'   => array( $price_start,$price_end),
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC',
                );
        }

        //Фильтрируем по к-во выводимых постов на странице
        if (isset($url['per_page'])){
            $per_page=(int)$url['per_page'];
            $_SESSION['fs_user_settings']['per_page']=$per_page;
        }

        // выполняем сортировку
        if (isset($url['order_type'])){

            switch ($url['order_type']){
                case 'price_asc': //сортируем по цене в возрастающем порядке

                $meta_query['price']=array(
                    'key'     => $this->conf->meta['price'],
                    'compare' => 'EXISTS',
                    'type'    => 'NUMERIC',
                    );
                $orderby[]='price';
                $order='ASC';
                break;
                case 'price_desc': //сортируем по цене в спадающем порядке
                $meta_query['price']=array(
                    'key'     => $this->conf->meta['price'],
                    'compare' => 'EXISTS',
                    'type'    => 'NUMERIC',
                    );
                $orderby[]='price';
                $order='DESC';
                break;
                case 'name_asc': //сортируем по названию по алфавиту
                $orderby[]='title';
                $order='ASC';
                break;
                case 'name_desc': //сортируем по названию по алфавиту в обратном порядке
                $orderby[]='title';
                $order='DESC';
                break;
                case 'field_action': //сортируем по наличию акции
                $meta_query['action_price']=array(
                    'key'     => $this->conf->meta['action_price'],
                    'compare' => '>',
                    'value'=>0,
                    'type'    => 'DECIMAL',
                    );
                break;

            }
        }

        //Фильтруем по свойствам (атрибутам)
        if (!empty($_REQUEST['attributes'])){
            $tax_query = array(
                'taxonomy' => 'product-attributes',
                'field' => 'id',
                'terms' => array_values($_REQUEST['attributes']),
                'operator'=> 'IN'
                );
            $query->tax_query->queries[] = $tax_query;
            $query->query_vars['tax_query'] = $query->tax_query->queries;
        }

        $query->set('posts_per_page',$per_page);
        $query->set('meta_query',$meta_query);
        $query->set('orderby',implode(' ',$orderby));
        $query->set( 'order',$order);
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
        $req=isset($_REQUEST['per_page']) ? $_REQUEST['per_page'] : get_option("posts_per_page");
        $nonce=wp_create_nonce('fast-shop');
        if(count($post_count)){
            $filter = '<select name="post_count" onchange="document.location=this.options[this.selectedIndex].value">';
            foreach ($post_count as $key => $count) {
                $filter.= '<option value="'.add_query_arg(array("fs_filter"=>$nonce,"per_page"=>$count)).'" '.selected($count,$req,false).'>'.$count.'</option>';
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


    }
}