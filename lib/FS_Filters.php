<?php
namespace FS;
/**
 * Class FS_Filters
 * @package FS
 */
class FS_Filters
{
    protected $conf;
	function __construct()
	{

		$this->conf=new FS_Config();
		add_action('pre_get_posts',array($this,'filter_curr_product'));
//		add_action( 'pre_get_posts', array($this,'fs_pre_posts_filter') );
        add_shortcode( 'fs_range_slider', array($this,'range_slider'));

	}

    /**
     * @param $query
     */
    public function filter_curr_product($query)
	{
		if(!isset($_REQUEST['fs_filter'])) return;

        //Фильтрируем по значениям диапазона цен
		if (isset($_REQUEST['price_start']) && isset($_REQUEST['price_end'])) {
			$price_start=(int)$_REQUEST['price_start'];
			$price_end=(int)$_REQUEST['price_end'];
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
		if (isset($_REQUEST['posts_per_page'])){
		    $per_page=(int)$_REQUEST['posts_per_page'];
            $_SESSION['fs_user_settings']['posts_per_page']=$per_page;
            $query->set('posts_per_page',$per_page);
        }
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

	public function attr_group_filter($group,$type='option',$option_default='Выберите значение')
	{
		$fs_atributes=get_option('fs-attr-group');
		if ($fs_atributes && $type=='option') {
			echo '<select name="product_color" id="product_color" onchange="document.location=this.options[this.selectedIndex].value"><option value="">'.$option_default.'</option>';
			foreach ($fs_atributes[$group]['attributes'] as $key => $value) {
				echo '<option value="'.add_query_arg(array('fs_filter'=>1,'attr'=>array($group=>$value)),esc_url($_SERVER['REQUEST_URI'])).'">'.$value.'</option>';
			}
			echo '</select>';
		}	
		if ($fs_atributes && $type=='list') {
			echo '<ul>';
			foreach ($fs_atributes[$group]['attributes'] as $key => $value) {
				echo '<li><a href="'.add_query_arg(array('fs_filter'=>1,'attr'=>array($group=>$value)),esc_url($_SERVER['REQUEST_URI'])).'">'.$value.'</a></li>';
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
		$req=(int)$_REQUEST['posts_per_page'];
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

}