<h2>Мои данные</h2>
<form  action="" name="fs-profile-edit" method="post">
<p class="form-info"></p>
<?php wp_nonce_field('fast-shop') ?>
<input type="hidden" name="action" value="fs_profile_edit">
<h3 class="form-block-t">Обязательные поля</h3> 
    <div class="form-group">
        <label for="fs-full-name"><?php _e('Full Name','fast-shop') ?></label>
        <input type="text" name="fs-form[fs-full-name]" class="form-control" id="fs-full-name" value="<?php echo  get_user_meta($user->ID,'fs-full-name',1) ?>" required title="<?php _e('required field','fast-shop') ?>">
    </div> 
    <div class="form-group">
        <label for="fs-phone"><?php _e('Phone','fast-shop') ?></label>
        <input type="tel" name="fs-form[fs-phone]" class="form-control" id="fs-phone" value="<?php echo  get_user_meta($user->ID,'fs-phone',1) ?>" required title="<?php _e('required field','fast-shop') ?>">
    </div>
    <div class="form-group">
        <label for="fs-email"><?php _e('Email','fast-shop') ?></label>
        <input type="email" name="fs-email" class="form-control" id="fs-email" value="<?php echo $user->user_email ?>" required title="<?php _e('enter a valid email','fast-shop') ?>">
    </div> 
    <div class="form-group">
        <label for="fs-login"><?php _e('Login','fast-shop') ?></label>
        <input type="text" name="fs-login" class="form-control" id="fs-login" value="<?php echo $user->user_login ?>" readonly>
    </div>
    <div class="form-group">
        <label for="fs-password"><?php _e('New password','fast-shop') ?></label>
        <input type="password" name="fs-password" class="form-control" id="fs-password">
    </div>
    <div class="form-group">
        <label for="fs-repassword"><?php _e('Repeat password','fast-shop') ?></label>
        <input type="password" name="fs-repassword" class="form-control" id="fs-repassword">
    </div>
   <h3 class="form-block-t">Дополнительная информация</h3> 
    <div class="form-group">
        <label for="fs-delivery"><?php _e('Shipping method','fast-shop') ?></label>
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
        <label for="fs-city"><?php _e('City','fast-shop') ?></label>
        <input type="text" name="fs-form[fs-city]"  class="form-control" id="fs-city" value="<?php echo  get_user_meta($user->ID,'fs-city',1) ?>">
    </div>
    <div class="form-group">
        <label for="fs-adress"><?php _e('Shipping address','fast-shop') ?></label>
        <input type="text" name="fs-form[fs-adress]"  class="form-control" id="fs-adress" value="<?php echo  get_user_meta($user->ID,'fs-adress',1) ?>">
    </div>
    <div class="form-group form-group-last">
        <label for="fs-payment"><?php _e('Payment method','fast-shop') ?></label>
        <select name="fs-form[fs-payment]" id="fs-payment" class="form-control">
            <?php $payments=get_terms('fs-payment-methods',array('hide_empty'=>false)) ?>
            <?php if ($payments): ?>
                <?php foreach ($payments as $key => $payment): ?>
                    <option value="<?php echo $payment->term_id ?>" <?php selected($payment->term_id,get_user_meta($user->ID,'fs-payment',1)) ?>><?php echo $payment->name ?></option>
                <?php endforeach ?>
            <?php endif ?>
        </select>
    </div>
    <button type="submit" class="button-t1"><?php _e('Save','fast-shop') ?> <img src="/wp-content/plugins/f-shop/assets/img/heart.svg" alt="preloader" class="fs-preloader"></button>
</form>