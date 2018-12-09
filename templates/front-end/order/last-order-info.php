<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 07.05.2018
 * Time: 13:00
 */
?>
<ul class="<?php echo esc_attr( $class ) ?>-contacts">
  <li>
    <span><?php esc_html_e('Name','f-shop') ?>: </span>
	  <?php echo esc_html( $order->user['first_name'] ); ?>
  </li>
  <li>
    <span><?php esc_html_e('Surname','f-shop') ?>: </span>
	  <?php echo esc_html( $order->user['last_name'] ); ?>
  </li>
  <li>
    <span><?php esc_html_e('Email','f-shop') ?>: </span>
	  <?php echo esc_html( $order->user['email'] ); ?>
  </li>
  <li>
    <span><?php esc_html_e('Phone number','f-shop') ?>: </span>
	  <?php echo esc_html( $order->user['phone'] ); ?>
  </li>
  <li>
    <span><?php esc_html_e('City','f-shop') ?>: </span>
	  <?php echo esc_html( $order->user['city'] ); ?>
  </li>
  <li>
    <span><?php esc_html_e('Type of delivery','f-shop') ?>: </span>
	  <?php echo esc_html( $order->delivery['method'] ) ?>
  </li>
  <li>
    <span><?php esc_html_e('Payment type','f-shop') ?>: </span>
	  <?php echo esc_html( $order->payment ) ?>
  </li>
</ul>
