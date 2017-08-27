<p>
  Рекомендуем заполнить все данные в меню "редактировать профиль". В случае повторного заказа, все данные подставятся
  автоматически. Мы не предоставляем ваши данные третьим лицам!
</p>
<ul class="infoList">
  <li>
    <span>Имя пользователя: </span><?php echo $user->nickname ?>
  </li>
  <li>
    <span>Отображаемое имя: </span><?php echo $user->display_name ?>
  </li>
  <li>
    <span>Имя: </span><?php echo $user->first_name ?>
  </li>
  <li>
    <span>Фамилия: </span><?php echo $user->last_name ?>
  </li>
  <li>
    <span>Электронная почта: </span><?php echo $user->user_email ?>
  </li>
  <li>
    <span>Номер телефона: </span><?php echo $user->phone ?>
  </li>
  <li>
    <span>Страна: </span><?php echo $user->country ?>
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
  <li>
    <span>Пол: </span><?php _e( $user->gender, 'fast-shop' ) ?>
  </li>
  <li>
    <span>Предпочитаемый способ оплаты: </span><?php echo $user->pay_method ?>
  </li>
  <li>
    <span>Предпочитаемый способ доставки: </span><?php echo $user->del_method ?>
  </li>
  <li>
    <span>Веб сайт: </span><?php echo $user->user_url ?>
  </li>
</ul>