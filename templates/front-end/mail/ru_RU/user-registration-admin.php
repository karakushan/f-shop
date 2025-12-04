<?php
/**
 * @var string $first_name
 * @var string $email
 * @var string $site_name
 */
?>
<h3>Новый пользователь <?php echo ! empty( $first_name ) ? esc_html( $first_name ) : '' ?> (<?php echo $email ?>)
    зарегистрирован на вашем сайте "<?php echo esc_html( $site_name ) ?>"!</h3>
<p>Это информационное сообщение, оно не требует ответа.</p>