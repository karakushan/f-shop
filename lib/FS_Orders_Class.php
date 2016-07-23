<?php 
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Класс заказов
*/
class FS_Orders_Class
{
	public $order_status=array(
		'0'=>'ожидает подтверждения',
		'1'=>'в ожидании оплаты',
		'2'=>'оплачен',
		'3'=>'отменён'
		);
	
	function __construct()
	{
		add_action( 'init',array(&$this, 'order_status_change'));
	}

	//Получаем все заказы в виде объекта
	public function get_orders()
	{
		global $wpdb,$fs_config;
		$table_name=$fs_config['table_name'];
		$per_page=15;
		if (isset($_SESSION['pagination'])) {
			$per_page=$_SESSION['pagination'];
		}
		
		$offset=1;
		if (isset($_GET['tab'])) {
			$offset=$_GET['tab'];
		}

		$offset=$offset*$per_page-$per_page;

		

		$results=$wpdb->get_results("SELECT * FROM  $table_name ORDER BY id DESC LIMIT $offset,$per_page");
		return $results;
	}


	public function order_pagination($class='')
	{
		global $wpdb,$fs_config;
		$table_name=$fs_config['table_name'];
		$per_page=15;
		if (isset($_SESSION['pagination'])) {
			$per_page=$_SESSION['pagination'];
		}
		$all_orders= $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
		$pages=$all_orders/$per_page+1;
		
		if ($all_orders>$per_page) {
			echo "<ul class=\"$class\">";
			for ($i=1; $i <=$pages ; $i++) { 
				if (!isset($_GET['tab'])) $_GET['tab']=1; 
				if ($_GET['tab']==$i) {
					$active='class="active"';
				}else{
					$active='';
				}
				echo "<li><a href=\"".add_query_arg(array('tab'=>$i))."\" $active>$i</a></li>";
			}
			echo "</ul>";
		}
	}



	//Отображает статус заказа в удобочитаемом виде
	public function order_status($status)
	{
		foreach ($this->order_status as $key => $value) {
			if ($status==$key) {
				$res=$value;
			}
		}
		return $res;
	}	



	//Получаем объект одного заказа
	public function get_order($id='')
	{
		global $wpdb,$fs_config;
		$table_name=$fs_config['table_name'];
		$res=$wpdb->get_row("SELECT * FROM $table_name WHERE id =$id");
		return $res;
	}

	public function order_status_change()
	{
		global $wpdb,$fs_config;
		$upd=false;
		$table_name=$fs_config['table_name'];
		if (isset($_GET['action']) && $_GET['action']=='edit') {
			if(isset($_GET['id']) || isset($_GET['status'])){
				$upd=$wpdb->update( 
					$table_name,
					array( 'status'=>$_GET['status']),
					array( 'id' => $_GET['id'] ),
					array( '%d' ),
					array( '%d' )
					);
			}

			if ($upd) {
				$status=$this->order_status($_GET['status']);
				$order=$this->get_order($_GET['id'] );
				$user_mesage='Статус заказа #'.$_GET['id'].' изменён на "'.$status.'". ';
				if ($_GET['status']==1) {
					$user_mesage.="Вы можете оплатить заказ сейчас если выбрали способ оплаты \"предоплата\" или в момент получения заказа";
				}
				$subject='Уведомление о изменении статуса заказа  #'.$_GET['id'];
				 $headers[] = 'Content-type: text/html; charset=utf-8'; // в виде массива
				 wp_mail( $order->email,$subject,$user_mesage, $headers );
				 wp_redirect(remove_query_arg( array('action','id','status')));
				}
			}
		}
	} ?>