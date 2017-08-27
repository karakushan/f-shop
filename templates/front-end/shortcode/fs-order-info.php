<ul class="<?php echo $class ?>">
  <li>
    <b>Заказ оформлен на следующие контактные данные:</b>
  </li>
  <li>
    <span>Имя: </span>
	  <?php echo $order->user['first_name']; ?>
  </li>
  <li>
    <span>Фамилия: </span>
	  <?php echo $order->user['last_name']; ?>
  </li>
  <li>
    <span>Электронная почта: </span>
	  <?php echo $order->user['email']; ?>
  </li>
  <li>
    <span> Номер телефона: </span>
	  <?php echo $order->user['phone']; ?>
  </li>
  <li>
    <span> Город: </span>
	  <?php echo $order->user['city']; ?>
  </li>
  <li>
    <span> Тип доставки: </span>
	  <?php echo $order->delivery['method'] ?>
  </li>
  <li>
    <span>Тип оплаты: </span>
	  <?php echo $order->payment ?>
  </li>
</ul>