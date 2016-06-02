<?php 
/**
*  Обработка POST или GET запросов
*/
class FS_Action_Class
{
	
	function __construct()
	{
		add_action('init', array(&$this,'fs_catch_action'), 2);
	}
	public function fs_catch_action()
	{
		if (isset($_REQUEST['fs_action'])) {
			if (!wp_verify_nonce($_REQUEST['fs_request'],'fs_action')) exit();
			$action=$_REQUEST['fs_action'];
			switch ($action) {
				// обновление к-ва товаров
				case 'update_cart':
				foreach ($_REQUEST['cart'] as $key => $value) {
					$_SESSION['cart'][$key]=array('count'=>$value);
				}
				break;
				//удаляем продукт из корзины
				case 'delete_product':
				$fs_cart=new FS_Cart_Class();
				$fs_cart->fs_remove_product($_REQUEST['product_id'],true);
				break;

				default:
				exit;
				break;
			}
		}

		
	}
} ?>