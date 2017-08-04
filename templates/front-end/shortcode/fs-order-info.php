<?php
$user     = get_post_meta( $order->ID, '_user', 0 );
$user     = $user[0];
$delivery = get_post_meta( $order->ID, '_delivery', 0 );
$delivery = $delivery[0];
$payment  = get_post_meta( $order->ID, '_payment', 1 );
?>
<ul class="<?php echo $class ?>">
  <li>
    <b>Заказ оформлен на следующие контактные данные:</b>
  </li>
  <li>
    <span>Ваше имя: </span>
		<?php echo $user['first_name']; ?> <?php echo $user['last_name']; ?>
  </li>
  <li>
    <span>Электронная почта: </span>
		<?php echo $user['email']; ?>
  </li>
  <li>
    <span> Номер телефона: </span>
		<?php echo $user['phone']; ?>
  </li>
  <li>
    <span> Город: </span>
		<?php echo $user['city']; ?>
  </li>
  <li>
    <span> Тип доставки: </span>
		<?php echo get_term_field( 'name', $delivery['method'], 'fs-delivery-methods' ) ?>
  </li>
  <li>
    <span>Тип оплаты: </span>
		<?php echo get_term_field( 'name', $payment, 'fs-payment-methods' ) ?>
  </li>
</ul>