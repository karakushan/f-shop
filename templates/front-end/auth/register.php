<h2><?php esc_html_e('New User Registration', 'f-shop'); ?></h2>
<p><?php esc_html_e('Already registered?', 'f-shop'); ?>
    <a href="<?php echo esc_url(get_permalink(fs_option('page_auth'))); ?>">
        <?php esc_html_e('Log in', 'f-shop'); ?></a></p>
<form action="" name="fs-profile-create" method="post">
    <p class="form-info"></p>
    <?php wp_nonce_field('f-shop') ?>
    <input type="hidden" name="action" value="fs_profile_create">
    <h3 class="form-block-t"><?php esc_html_e('Required fields', 'f-shop'); ?></h3>
    <div class="form-group">
        <label for="fs-full-name"><?php esc_html_e('Full Name', 'f-shop') ?></label>
        <input type="text" name="fs-form[fs-full-name]" class="form-control" id="fs-full-name"
               value="<?php echo esc_attr(get_user_meta($user->ID, 'fs-full-name', 1)) ?>" required
               title="<?php esc_attr_e('required field', 'f-shop') ?>">
    </div>
    <div class="form-group row">
        <div class="col-md-6">
            <label for="fs-phone"><?php esc_html_e('Phone', 'f-shop') ?></label>
            <input type="tel" name="fs-form[fs-phone]" class="form-control" id="fs-phone"
                   value="<?php echo esc_attr(get_user_meta($user->ID, 'fs-phone', 1)) ?>" required
                   title="<?php esc_attr_e('required field', 'f-shop') ?>">
        </div>
        <div class="col-md-6">

            <label for="fs-email"><?php esc_html_e('Email', 'f-shop') ?></label>
            <input type="email" name="fs-email" class="form-control" id="fs-email"
                   value="<?php echo esc_attr($user->user_email) ?>" required
                   title="<?php esc_attr_e('enter a valid email', 'f-shop') ?>">

        </div>
    </div>
    <div class="form-group">
        <label for="fs-login"><?php esc_html_e('Login', 'f-shop') ?></label>
        <input type="text" name="fs-login" class="form-control" id="fs-login"
               value="<?php echo esc_attr($user->user_login) ?>"
               required title="<?php esc_attr_e('required field', 'f-shop') ?>">
    </div>
    <div class="form-group row">
        <div class="col-md-6">
            <label for="fs-password"><?php esc_html_e('Password', 'f-shop') ?></label>
            <input type="password" name="fs-password" class="form-control" id="fs-password" required
                   title="<?php esc_attr_e('required field', 'f-shop') ?>">
        </div>

        <div class="col-md-6">
            <label for="fs-repassword"><?php esc_html_e('Repeat password', 'f-shop') ?></label>
            <input type="password" name="fs-repassword" class="form-control" id="fs-repassword"
                   title="<?php esc_attr_e('password and repeat password must match', 'f-shop') ?>" required>
        </div>
    </div>
    <h3 class="form-block-t"><?php esc_html_e('Additional Information', 'f-shop'); ?></h3>
    <div class="form-group">
        <label for="fs-delivery"><?php esc_html_e('Shipping method', 'f-shop') ?></label>
        <select name="fs-form[fs-delivery]" id="fs-delivery" class="form-control">
            <?php $shipings = get_terms('fs-delivery-methods', array('hide_empty' => false)) ?>
            <?php if ($shipings): ?>
                <?php foreach ($shipings as $key => $shiping): ?>
                    <option value="<?php echo esc_attr($shiping->term_id) ?>" <?php selected($shiping->term_id, get_user_meta($user->ID, 'fs-delivery', 1)) ?>><?php echo esc_html($shiping->name) ?></option>
                <?php endforeach ?>
            <?php endif ?>
        </select>

    </div>
    <div class="form-group">
        <label for="fs-city"><?php esc_html_e('City', 'f-shop') ?></label>
        <input type="text" name="fs-form[fs-city]" class="form-control" id="fs-city"
               value="<?php echo esc_attr(get_user_meta($user->ID, 'fs-city', 1)) ?>">
    </div>
    <div class="form-group">
        <label for="fs-adress"><?php esc_html_e('Shipping address', 'f-shop') ?></label>
        <input type="text" name="fs-form[fs-adress]" class="form-control" id="fs-adress"
               value="<?php echo esc_attr(get_user_meta($user->ID, 'fs-adress', 1)) ?>">
    </div>
    <div class="form-group form-group-last">
        <label for="fs-payment"><?php esc_html_e('Payment method', 'f-shop') ?></label>
        <select name="fs-form[fs-payment]" id="fs-payment" class="form-control">
            <?php $payments = get_terms('fs-payment-methods', array('hide_empty' => false)) ?>
            <?php if ($payments): ?>
                <?php foreach ($payments as $key => $payment): ?>
                    <option value="<?php echo esc_attr($payment->term_id) ?>" <?php selected($payment->term_id, get_user_meta($user->ID, 'fs-payment', 1)) ?>><?php echo esc_attr($payment->name) ?></option>
                <?php endforeach ?>
            <?php endif ?>
        </select>
    </div>
    <button type="submit" class="button-t1"><?php esc_html_e('Send', 'f-shop') ?></button>
</form>