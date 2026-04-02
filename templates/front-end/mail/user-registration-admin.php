<?php
/**
 * @var string $first_name
 * @var string $email
 * @var string $site_name
 */
?>
<h3>
    <?php
    echo esc_html(
        sprintf(
            __('New user %1$s (%2$s) on your website "%3$s"!', 'f-shop'),
            !empty($first_name) ? $first_name : '',
            $email,
            $site_name
        )
    );
    ?>
</h3>
<p><?php esc_html_e('This is an informational message and does not require a reply.', 'f-shop'); ?></p>
