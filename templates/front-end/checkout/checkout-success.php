<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 03.06.2018
 * Time: 23:33
 */
?>
<p class="fs-info-block"><i class="fas fa-info-circle"></i>
	<?php printf( __( 'Order #%s was successfully created. Check your mail for further instructions.', 'f-shop' ), esc_attr(\FS\FS_Orders::get_last_order_id()) ) ?>
</p>

