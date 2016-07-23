<?php
namespace FS;
/**
 * Class FS_Filters
 * @package FS
 */
class FS_Filters
{
	function __construct()
	{
		global $fs_config;
		$this->conf=$fs_config;
		add_action('pre_get_posts',array($this,'filter_curr_product'));

	}

	public function filter_curr_product($query)
	{
		if(!isset($_REQUEST['fs-filter'])) return;
		
		if (isset($_REQUEST['price_start']) && isset($_REQUEST['price_end'])) {
			$price_start=(int)$_REQUEST['price_start'];
			$price_end=(int)$_REQUEST['price_end'];
			$query->set('post_type','product');
			$query->set('meta_query',array(
				array(
					'key'     => $this->conf['plugin_meta']['price'],
					'value'   => array( $price_start,$price_end),
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
					)
				)

			);
			$query->set('orderby','meta_value_num');
		}
	}//end filter_curr_product()

	public function range_slider()
	{
		$slider='
		<div class="range">
			<div id="slider-range" data-uri="'.fs_parse_url().'"></div>
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
				echo '<option value="'.add_query_arg(array('fs-filter'=>1,'attr:'.$group=>$key)).'">'.$value.'</option>';
			}
			echo '</select>';
		}	
		if ($fs_atributes && $type=='list') {
			echo '<ul>';
			foreach ($fs_atributes[$group]['attributes'] as $key => $value) {
				echo '<li><a href="'.add_query_arg(array('fs-filter'=>1,'attr:'.$group=>$value)).'">'.$value.'</a></li>';
			}
			echo '</ul>';
		}
	}//end attr_group_filter()
}