<?php
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 /**
* Клас для обработки ajax запросов
*/

class FS_Ajax_Class
{
	
	function __construct()
	{
		add_action('wp_ajax_order_send',array(&$this,'order_send_ajax') );
		add_action('wp_ajax_nopriv_order_send',array(&$this,'order_send_ajax') );

	}



	function order_send_ajax()
	{
		ini_set('error_reporting', E_ALL);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);


		if ( !wp_verify_nonce( $_REQUEST['_wpnonce']) )
			die ( 'errors');


		global $wpdb, $fs_config;
		$wpdb->show_errors();
		$order_id='';


		$fs_delivery=new FS_Delivery_Class();
		$insert=$wpdb->insert(
			$fs_config['table_name'],
			array( 
				'name' =>$_REQUEST['name'],
				'email' =>$_REQUEST['email'],
				'status' =>0,
				'telephone' =>$_REQUEST['telefon'],
				'comments' =>'',
				'delivery' =>$_REQUEST['delivery'],
				'products' =>serialize($_SESSION['cart']),
				'summa' =>fs_total_amount(false)
				),
			array( '%s','%s','%d','%s','%s','%s','%s','%d')
			);

		$products='';
		foreach ($_SESSION['cart'] as $key => $count) { 

			$products.='<li>'.get_the_title($key).' - '.fs_get_price($key).' '.get_option( 'currency_icon', 'грн.' ).'</li>';
		}

		
		$order_id=$wpdb->insert_id;
		$_SESSION['last_order_id']=$order_id;
		if (!$order_id) {
			$wpdb->print_error();
		}
		
		$user_mesage='<p>Поздравляем '.$_REQUEST['name'].'. Вы осуществили покупку '.fs_product_count().' тов. на сайте '.get_bloginfo('name').' на сумму '.fs_total_amount(false).' '.get_option( 'currency_icon', '$' ).'.</p>';
		$user_mesage.='<h3>Список товаров:</h3><ul>';
		$user_mesage.=$products;
		$user_mesage.='</ul>';
		$user_mesage.='<p>Ваш номер заказа #'.$order_id.'. Ожидайте подтверждения заявки или звонка менеджера!</p>';


		//Отсылаем письмо с данными заказа заказчику
		$headers[] = 'Content-type: text/html; charset=utf-8';
		wp_mail(  $_REQUEST['email'],'Заказ товара на сайте '.get_bloginfo('name'),$user_mesage, $headers ); 


		$del=$fs_delivery->get_delivery($_REQUEST['delivery']);
		$admin_mesage='<p>Поздравляем на вашем сайте '.get_bloginfo('name').'. была осуществлена  покупка '.fs_product_count().' тов. на на сумму '.fs_total_amount(false).' '.get_option( 'currency_icon', 'грн.' ).'.</p>';
		$admin_mesage.='<h3>Список товаров:</h3><ul>';
		$admin_mesage.=$products;
		$admin_mesage.='</ul>';
		$admin_mesage.='<h3>Данные заказчика: </h3><ul>';
		$admin_mesage.='<li>Номер заказа: #'.$order_id.'</li>';
		$admin_mesage.='<li>Имя: '.$_REQUEST['name'].'</li>';
		$admin_mesage.='<li>E-mail: '.$_REQUEST['email'].'</li>';
		$admin_mesage.='<li>Телефон: '.$_REQUEST['telefon'].'</li>';
		$admin_mesage.='<li>Способ доставки: '. $del.'</li>';
		$admin_mesage.='<li>Как доставить: '.$_REQUEST['delivery_other'].'</li>';
		$admin_mesage.='<li>Город: '.$_REQUEST['billing_city'].'</li>';
		$admin_mesage.='<li>Адрес: '.$_REQUEST['adress'].'</li>';
		$admin_mesage.='</ul>';
		$admin_mesage.='<p>Внимание!!! Вам необходимо подвердить заказ в панели администрирования изменив статус на "в ожидании оплаты". </p>';

		//Отсылаем письмо с данными заказа админу
		wp_mail(get_bloginfo('admin_email' ),'Заказ товара на сайте '.get_bloginfo('name'),$admin_mesage, $headers );

		unset($_SESSION['cart']);
		
		exit;

	}


} ?>