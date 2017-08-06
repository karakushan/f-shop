<table class="wp-list-table widefat fixed striped order-user">
  <tbody>
  <tr>
    <th>ID</th>
    <td><?php echo $user['id'] ?></td>
  </tr>
  <tr>
    <th>Имя</th>
    <td><?php echo $user['first_name'] ?></td>
  </tr>
  <tr>
    <th>Фамилия</th>
    <td><?php echo $user['last_name'] ?></td>
  </tr>
  <tr>
    <th>Телефон</th>
    <td><?php echo $user['phone'] ?></td>
  </tr>
  <tr>
    <th>E-mail</th>
    <td><?php echo $user['email'] ?></td>
  </tr>
  <tr>
    <th>Город</th>
    <td><?php echo $user['city'] ?></td>
  </tr>
  <tr>
    <th>Способ доставки</th>
    <td><?php echo get_term_field( 'name', $delivery['method'], 'fs-delivery-methods' ) ?></td>
  </tr>
  <tr>
    <th>Отделение службы доставки:</th>
    <td><?php echo $delivery['secession'] ?></td>
  </tr>
  <tr>
    <th>Адрес доставки</th>
    <td><?php echo $delivery['adress'] ?></td>
  </tr>
  <tr>
    <th>Способ оплаты</th>
    <td><?php echo get_term_field( 'name', $payment, 'fs-payment-methods' ) ?></td>
  </tr>
  <tr>
    <th>Комментарий к заказу</th>
    <td><?php echo get_post_meta( $post->ID, '_comment', 1 ) ?></td>
  </tr>

  </tbody>
</table>
