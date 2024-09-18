<?php
/**
 * @var string $first_name
 * @var string $login
 * @var string $email
 * @var string $site_name
 * @var string $cabinet_url
 */
?>

<h3>Вітаємо <?php echo esc_html( $first_name ) ?></h3>
<p>Ви успішно зареєструвалися на сайті «<?php echo esc_html( $site_name ) ?>»</p>
<h4>Особистий кабінет знаходиться за адресою:</h4>
<p><a href="<?php echo esc_url( $cabinet_url ) ?>"><?php echo esc_html( $cabinet_url ) ?></a></p>
<p>Для входу використовуйте E-mail та пароль з якими Ви проходили реєстрацію</p>
<p>Якщо Ви не реєструвалися на сайті «<?php echo esc_html( $site_name ) ?>», то просто проігноруйте цей лист.</p>