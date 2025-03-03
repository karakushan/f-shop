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

<h3>Вітаємо <?php echo ! empty( $first_name ) ? esc_html( $first_name ) : '' ?>!</h3>
<p>Ви успішно зареєструвалися на сайті «<?php echo esc_html( $site_name ) ?>»</p>
<h4>Особистий кабінет знаходиться за адресою:</h4>
<p><a href="<?php echo esc_url( $cabinet_url ) ?>"><?php echo esc_html( $cabinet_url ) ?></a></p>
<h4>Для входу використовуйте наступні дані:</h4>
<p><?php printf( 'Ваш логін: %s', $login ) ?></p>
<p><?php printf( 'Ваш пароль: %s', $password ) ?></p>

<p>Якщо це були не Ви, то просто проігноруйте цей лист.</p>