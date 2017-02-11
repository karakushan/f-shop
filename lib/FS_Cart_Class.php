<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 /**
* Класс для работы с корзиной
*/
class FS_Cart_Class
{
	
	function __construct()
	{
		add_action('init', array(&$this,'fast_shop_init_session'), 1);

		add_action('wp_ajax_add_to_cart', array(&$this,'add_to_cart_ajax'));
		add_action('wp_ajax_nopriv_add_to_cart', array(&$this,'add_to_cart_ajax'));

		//Обновление корзины ajax
		add_action('wp_ajax_update_cart', array(&$this,'update_cart_ajax'));
		add_action('wp_ajax_nopriv_update_cart', array(&$this,'update_cart_ajax'));//

		//Удаление товара из корзины ajax
		add_action('wp_ajax_delete_product', array(&$this,'delete_product_ajax'));
		add_action('wp_ajax_nopriv_delete_product', array(&$this,'delete_product_ajax'));

		
		
	}



	//Инициализируем сессии
	function fast_shop_init_session()
	{
		@session_start();

		// регистриуем глобальную переменную
		global $cart;
		if (!empty($_SESSION['cart'])) {
			foreach ($_SESSION['cart'] as $key => $count){
				if ($key==0) continue;
				$price=fs_get_price($key);
				$price_show=apply_filters('fs_price_format',$price);
				$count=(int)$count['count'];
				$all_price=$price*$count;
				$all_price=apply_filters('fs_price_format',$all_price);

				$cart[$key]=array(
					'id'=>$key,
					'name'=>get_the_title($key),
					'count'=>$count,
					'link'=>get_permalink($key),
					'price'=> $price_show,
					'all_price'=>$all_price,
					'currency'=>fs_currency()
					);
			}
		}

	}

	// ajax обработка добавления в корзину
	function add_to_cart_ajax()
	{
		$product_id=(int)$_REQUEST['post_id'];
		$attr=array_map('sanitize_text_field',$_REQUEST['attr']);
		$count=(int)$attr['count'];
		if (isset($_SESSION['cart'][$product_id])) {
			$count_sess=$_SESSION['cart'][$product_id]['count'];
			$_SESSION['cart'][$product_id]=array(
				'count'=>$count_sess+$count,
				'attr'=>$attr	
				);
		}else{
			$_SESSION['cart'][$product_id]=array(
				'count'=>$count,
				'attr'=>$attr		
				);
		}
		fs_cart_widget();
		exit;

	}

//Метод удаляет конкретный товар или все товары из корзины покупателя
	public function fs_remove_product($product_id='',$redirect=false)
	{
		if ($product_id=='') {
			unset($_SESSION['cart']);
		} else {
			$product_id=intval($_REQUEST['product_id']);
			unset($_SESSION['cart'][$product_id]);
		}

		if ($redirect===true) {
			wp_redirect(remove_query_arg(array('fs_action','fs_request','product_id')));
			exit();
		}
	}

	//обновление товара в корзине аяксом
	public function update_cart_ajax()
	{
		$product_id=(int)$_REQUEST['product'];
		$product_count = (int)$_REQUEST['count'];
		if ($_SESSION['cart']) {
			$_SESSION['cart'][$product_id]['count']=$product_count;
		}
		exit;
	}

	//удаление товара в корзине аяксом
	public function delete_product_ajax()
	{
		$product_id=(int)$_REQUEST['product'];
		if ($_SESSION['cart']) {
			if ($_SESSION['cart'][$product_id]) {
				unset($_SESSION['cart'][$product_id]);
			}
		}
		exit;
	}


}