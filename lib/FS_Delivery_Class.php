<?php 
namespace FS;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Класс доставки
*/
class FS_Delivery_Class
{
	public $delivery;
	
	function __construct()
	{
		$this->delivery=get_option( 'fs_delivery');
		add_action( 'init',array(&$this,'add_delivery'));
		add_action( 'init',array(&$this,'delete_delivery'));
		
	}

	public function get_delivery($id)
	{
		$delivery=$this->delivery;
		return $delivery[$id]['name'];

	}

	//Получаем способы доставки в виде опций селект или списка
	public function list_delivery($type='select')
	{
		$key=0;
		if ($this->delivery) {
			switch ($type) {
				case 'select':
				if ($this->delivery) {
					echo "<select name=\"delivery\" id=\"delivery\">";
					echo "<option value=\"\">".__('Select the method','fast-shop')."</option>";
					foreach ($this->delivery as $key=>$del) {
						echo "<option value=\"$del[id]\">$del[name]</option>";
					}
					echo "</select>";
				}
				
				break;				
				case 'li':
				foreach ($this->delivery as $del) {
					echo "<li>$del[name]</li>";
				}
				break;				
				case 'radio':
				foreach ($this->delivery as$del) {
					
					
					if ($key==0){ $checked='checked="checked"';} else{ $checked='';}
					echo "<li>
					<input type=\"radio\" name=\"delivery\" value=\"$del[id]\" id=\"$del[id]\" $checked>
					<label for=\"$del[id]\">$del[name]</label>
				
				</li>";
				$key++;
			}
			break;
			default:	
			foreach ($this->delivery as $key=>$del) {
				echo "<option>$del[name]</option>";
			}
			break;
		}
	}
}

// добавляет новый способ доставки
public function add_delivery()
{
	$upd=false;
	if (isset($_POST['delivery_save'])) {
		$id=esc_sql($_POST['delivery_id'] );
		$delivery=$this->delivery;

		$delivery[$id]=array(
			'id'=>$id,
			'name'=>esc_sql($_POST['delivery_name'] ),
			'price'=>esc_sql($_POST['delivery_price'] )
			);
		$upd=update_option('fs_delivery',$delivery) ;

	}
	return $upd;
}
	//удаление способа доставки
public function delete_delivery()
{
	if (isset($_REQUEST['action']) && $_REQUEST['action']=='delete') {
		$id=esc_sql( $_REQUEST['id']);
		$delivery=$this->delivery;
		unset($delivery[$id]);
		$upd=update_option('fs_delivery',$delivery);
		if ($upd) {
			wp_redirect(remove_query_arg( array('action','id')));
		}

	}

}

} ?>