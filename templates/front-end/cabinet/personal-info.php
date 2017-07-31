<p>
  В личном кабинете Вы можете проверить текущее состояние корзины, ход выполнения заказов просмотреть или
  изменить личную информацию а также подписаться на новости или другие информационные рассылки сайта.
</p>
<ul class="infoList">
  <li>
    <span>Ваше имя: </span><?php echo $user->display_name ?>
  </li>
  <li>
    <span>Электронная почта: </span><?php echo $user->email ?>
  </li>
  <li>
    <span>Номер телефона: </span><?php echo $user->phone ?>
  </li>
  <li>
    <span>Город: </span><?php echo $user->city ?>
  </li>
  <li>
    <span>Адрес: </span><?php echo $user->adress ?>
  </li>
  <li>
    <span>Дата рождения: </span><?php echo date( 'd.m.Y', $user->birth_day ) ?>
  </li>
</ul>