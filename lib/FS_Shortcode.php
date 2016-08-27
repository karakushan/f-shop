<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
/**
* Класс шорткодов магазина
*/
class FS_Shortcode
{
	
	function __construct()
	{
		add_shortcode( 'fs_cart', array(&$this,'cart_shortcode'));
		add_shortcode( 'fs_order_info', array(&$this,'single_order_info'));
		add_shortcode( 'fs_last_order_id', array(&$this,'last_order_id'));
		add_shortcode( 'fs_review_form', array(&$this,'review_form'));
		add_shortcode( 'fs_checkout', array(&$this,'checkout_form'));

	}

			//Шорткод для отображения купленных товаров и оформления покупки
	public function cart_shortcode()
	{
		global $fs_config;

		$template_row_before=TEMPLATEPATH.'/fast-shop/cart/product-row-before.php';
		$plugin_row_before=$fs_config['plugin_path'].'templates/front-end/cart/product-row-before.php';

		$template_row=TEMPLATEPATH.'/fast-shop/cart/product-row.php';
		$plugin_row=$fs_config['plugin_path'].'templates/front-end/cart/product-row.php';

		$template_row_after=TEMPLATEPATH.'/fast-shop/cart/product-row-after.php';
		$plugin_row_after=$fs_config['plugin_path'].'templates/front-end/cart/product-row-after.php';

		$template_none_plugin=$fs_config['plugin_path'].'templates/front-end/cart/cart-empty.php';
		$template_none_theme=TEMPLATEPATH.'/fast-shop/cart/cart-empty.php';
		//получаем содержимое корзины (сессии)
		$carts=fs_get_cart();

		if (count($carts)) {
			if (file_exists($template_row_before)) {
				include ($template_row_before);
			} else {
				include ($plugin_row_before);
			}
			foreach ($carts as $cart){
				if (file_exists($template_row)) {
					include ($template_row);
				} else {
					include ($plugin_row);
				}
			}
			if (file_exists($template_row_after)) {
				include ($template_row_after);
			} else {
				include ($plugin_row_after);
			}
		}else{
			if (file_exists($template_none_theme)) {
				include ($template_none_theme);
			}else{
				include ($template_none_plugin);
			}
		}
	}

	//Шорткод показывает информацию о заказе
	public function single_order_info($order_id='')
	{
		if ($order_id=='')  $order_id=$_SESSION['last_order_id'];

		if (!is_numeric($order_id))  return;

		$order=new FS_Orders_Class();
		$delivery=new FS_Delivery_Class();
		$order_info=$order->get_order($order_id);

		$info="<table>
		<tr>
			<th>Ваше имя</th><td>".$order_info ->name."</td>
		</tr>
		<tr>
			<th>Электронная почта </th><td>".$order_info->email."</td>
		</tr>
		<tr>
			<th>Номер телефона </th><td>".$order_info->telephone."</td>
		</tr>
		<tr>
			<th>Способ доставки</th><td>".$delivery->get_delivery($order_info->delivery)."</td>
		</tr>
		<tr>
			<th>Общая сумма</th><td>".$order_info->summa." ".get_option('currency_icon','$')."</td>
		</tr>
		<tr>
			<th>Статус</th><td>".$order->order_status[$order_info->status]."</td>
		</tr>
	</table>";
	return $info;

}

//Возвращает id последнего заказа
public function last_order_id()
{
	$order_id=$_SESSION['last_order_id'];

	if (!is_numeric($order_id) || empty($order_id)) {
		return 0;
	}else{
		return $order_id;
	}
}

public function review_form()
{
	global $fs_config;
	require $fs_config['plugin_path'].'templates/back-end/review-form.php';
}

function checkout_form()
{
	global $fs_config;
	$checkout_form_theme=TEMPLATEPATH.'/fast-shop/checkout/checkout.php';
	$checkout_form_plugin=$fs_config['plugin_path'].'templates/front-end/checkout/checkout.php';
	if (file_exists($checkout_form_theme)) {
		include ($checkout_form_theme);
	} else {
		include ($checkout_form_plugin);
	}	
}

}//end classs