<?php
/**
 * @var string $first_name
 * @var string $login
 * @var string $password
 * @var string $email
 * @var string $site_name
 * @var string $cabinet_url
 */
?>

<h3>Поздравляем <?php echo ! empty( $first_name ) ? esc_html( $first_name ) : '' ?>!</h3>
<p>Вы успешно зарегистрировались на сайте «<?php echo esc_html( $site_name ) ?>»</p>
<h4>Личный кабинет находится по адресу:</h4>
<p><a href="<?php echo esc_url( $cabinet_url ) ?>"><?php echo esc_html( $cabinet_url ) ?></a></p>
<h4>Для входа используйте следующие данные:</h4>
<p><?php printf( 'Имя пользователя: %s', $login ) ?></p>
<p><?php printf( 'Пароль: %s', $password ) ?></p>

<p>Если это были не Вы, то просто проигнорируйте это письмо.</p>