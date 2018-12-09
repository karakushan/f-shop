<h2>Регистрация нового пользователя</h2>
<p>Уже зарегистрированны? <a href="'.esc_url(get_permalink(fs_option('page_auth'))).'">Выполнить вход</a></p>
<form  action="" name="fs-profile-create" method="post">
    <p class="form-info"></p>
    <?php wp_nonce_field('f-shop') ?>
    <input type="hidden" name="action" value="fs_profile_create">
    <h3 class="form-block-t">Обязательные поля</h3> 
    <div class="form-group">
        <label for="fs-full-name"><?php _e('Full Name','f-shop') ?></label>
        <input type="text" name="fs-form[fs-full-name]" class="form-control" id="fs-full-name" value="<?php echo  get_user_meta($user->ID,'fs-full-name',1) ?>" required title="<?php _e('required field','f-shop') ?>">
    </div> 
    <div class="form-group row">
      <div class="col-md-6">
        <label for="fs-phone"><?php _e('Phone','f-shop') ?></label>
        <input type="tel" name="fs-form[fs-phone]" class="form-control" id="fs-phone" value="<?php echo  get_user_meta($user->ID,'fs-phone',1) ?>" required title="<?php _e('required field','f-shop') ?>">
    </div>
    <div class="col-md-6">

        <label for="fs-email"><?php _e('Email','f-shop') ?></label>
        <input type="email" name="fs-email" class="form-control" id="fs-email" value="<?php echo $user->user_email ?>" required title="<?php _e('enter a valid email','f-shop') ?>">

    </div>
</div>
<div class="form-group">
    <label for="fs-login"><?php _e('Login','f-shop') ?></label>
    <input type="text" name="fs-login" class="form-control" id="fs-login" value="<?php echo $user->user_login ?>" required title="<?php _e('required field','f-shop') ?>">
</div>
<div class="form-group row">
    <div class="col-md-6">
        <label for="fs-password"><?php _e('Password','f-shop') ?></label>
        <input type="password" name="fs-password" class="form-control" id="fs-password" required title="<?php _e('required field','f-shop') ?>">
    </div>

    <div class="col-md-6">
        <label for="fs-repassword"><?php _e('Repeat password','f-shop') ?></label>
        <input type="password" name="fs-repassword" class="form-control" id="fs-repassword" title="<?php _e('password and repeat password must match','f-shop') ?>" required>
    </div>
</div>
<h3 class="form-block-t">Дополнительная информация</h3> 
<div class="form-group">
    <label for="fs-delivery"><?php _e('Shipping method','f-shop') ?></label>
    <select name="fs-form[fs-delivery]" id="fs-delivery" class="form-control">
        <?php $shipings=get_terms('fs-delivery-methods',array('hide_empty'=>false)) ?>
        <?php if ($shipings): ?>
            <?php foreach ($shipings as $key => $shiping): ?>
                <option value="<?php echo $shiping->term_id ?>" <?php selected($shiping->term_id,get_user_meta($user->ID,'fs-delivery',1)) ?>><?php echo $shiping->name ?></option>
            <?php endforeach ?>
        <?php endif ?>
    </select>

</div>
<div class="form-group">
    <label for="fs-city"><?php _e('City','f-shop') ?></label>
    <input type="text" name="fs-form[fs-city]"  class="form-control" id="fs-city" value="<?php echo  get_user_meta($user->ID,'fs-city',1) ?>">
</div>
<div class="form-group">
    <label for="fs-adress"><?php _e('Shipping address','f-shop') ?></label>
    <input type="text" name="fs-form[fs-adress]"  class="form-control" id="fs-adress" value="<?php echo  get_user_meta($user->ID,'fs-adress',1) ?>">
</div>
<div class="form-group form-group-last">
    <label for="fs-payment"><?php _e('Payment method','f-shop') ?></label>
    <select name="fs-form[fs-payment]" id="fs-payment" class="form-control">
        <?php $payments=get_terms('fs-payment-methods',array('hide_empty'=>false)) ?>
        <?php if ($payments): ?>
            <?php foreach ($payments as $key => $payment): ?>
                <option value="<?php echo $payment->term_id ?>" <?php selected($payment->term_id,get_user_meta($user->ID,'fs-payment',1)) ?>><?php echo $payment->name ?></option>
            <?php endforeach ?>
        <?php endif ?>
    </select>
</div>
<button type="submit" class="button-t1"><?php _e('Send','f-shop') ?> <img src="/wp-content/plugins/f-shop/assets/img/heart.svg" alt="preloader" class="fs-preloader"></button>
</form>