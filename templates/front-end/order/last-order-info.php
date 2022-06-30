<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 07.05.2018
 * Time: 13:00
 */
?>
<ul class="<?php echo esc_attr( $args['class'] ) ?>">
    <li>
        <span><?php esc_html_e( 'Name', 'f-shop' ) ?>: </span>
		<?php echo esc_html( $order->data->_user['first_name'] ); ?>
    </li>
    <li>
        <span><?php esc_html_e( 'Surname', 'f-shop' ) ?>: </span>
		<?php echo esc_html( $order->data->_user['last_name'] ); ?>
    </li>
    <li>
        <span><?php esc_html_e( 'Email', 'f-shop' ) ?>: </span>
		<?php echo esc_html( $order->data->_user['email'] ); ?>
    </li>
    <li>
        <span><?php esc_html_e( 'Phone number', 'f-shop' ) ?>: </span>
		<?php echo esc_html( $order->data->_user['phone'] ); ?>
    </li>
    <li>
        <span><?php esc_html_e( 'City', 'f-shop' ) ?>: </span>
		<?php echo esc_html( $order->data->_user['city'] ); ?>
    </li>
    <li>
        <span><?php esc_html_e( 'Type of delivery', 'f-shop' ) ?>: </span>
		<?php echo esc_html( get_term_field('name',$order->data->_delivery['method']) ) ?>
    </li>
    <li>
        <span><?php esc_html_e( 'Payment type', 'f-shop' ) ?>: </span>
		<?php echo esc_html( get_term_field('name',$order->data->_payment) ) ?>
    </li>
</ul>
