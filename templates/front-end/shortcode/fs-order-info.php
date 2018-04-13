<div class="<?php echo esc_attr( $class ) ?>">
  <h2>Детальная информация о заказе №<?php echo $order_id ?> </h2>
  <h3>Купленные товары:</h3>
  <div class="table-responsive">
    <table class="table ">
      <thead style="thead-dark">
      <tr>
        <th>#ID</th>
        <th>Фото</th>
        <th>Название</th>
        <th>Цена</th>
        <th>К-во</th>
        <th>Стоимость</th>
      </tr>
      </thead>
      <tbody>
	  <?php if ( ! empty( $order->items ) ): ?>
		  <?php foreach ( $order->items as $id => $item ): ?>
          <tr>
            <td><?php echo $id ?></td>
            <td class="thumb"><?php if ( has_post_thumbnail( $id ) )
					echo get_the_post_thumbnail( $id ) ?></td>
            <td><a href="<?php the_permalink( $id ) ?>" target="_blank"><?php echo get_the_title( $id ) ?></a></td>
            <td><?php do_action( 'fs_the_price', $id ) ?></td>
            <td><?php echo $item['count'] ?></td>
            <td><?php echo fs_row_price( $id, $item['count'] ) ?></td>
          </tr>
		  <?php endforeach; ?>
	  <?php endif; ?>
      <tfoot>
      <tr>
        <td colspan="5">Общая стоимость</td>
        <td><?php echo $order->sum ?><?php echo fs_currency() ?></td>
      </tr>
      </tfoot>
      </tbody>
    </table>
  </div>
  <h3>Контактные данные:</h3>
  <ul class="<?php echo esc_attr( $class ) ?>-contacts">
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
		<?php echo $order->payment ?> <a href="<?php echo esc_url( $order->payment_link ) ?>"
                                         class="btn btn-secondary btn-sm">оплатить сейчас</a>
    </li>
  </ul>
</div>