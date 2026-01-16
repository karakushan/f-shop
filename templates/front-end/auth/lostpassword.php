<?php
// Check if reset key and login are present in URL
$rp_key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$rp_login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';

// If key and login are present, show reset password form
if (!empty($rp_key) && !empty($rp_login)) {
    // Validate reset key
    $user = check_password_reset_key($rp_key, $rp_login);
    
    if (is_wp_error($user)) {
        $error_code = $user->get_error_code();
        $error_message = '';
        if ($error_code === 'expired_key') {
            $error_message = __('Password reset link has expired. Please request a new one.', 'f-shop');
        } else {
            $error_message = __('Invalid password reset link.', 'f-shop');
        }
        ?>
        <div class="fs-login-form">
            <div class="form-group msg-error">
                <?php echo esc_html($error_message); ?>
            </div>
            <div class="fs-login-links justify-center">
                <a href="<?php echo esc_url(get_permalink(fs_option('page_lostpassword'))); ?>" class="fs-login-link">
                    <?php esc_html_e('Request a new password reset link', 'umbrella'); ?>
                </a>
            </div>
        </div>
        <?php
    } else {
        // Show reset password form
        ?>
        <form method="post" class="fs-login-form"
              action="" x-ref="resetPasswordForm" x-data="{ errors: [], msg: '' }"
              x-on:submit.prevent="Alpine.store('FS').resetPass($event).then((r)=>{
            if(r.success===false) {
                errors=typeof r.data.errors!=='undefined' ? r.data.errors : [];
                msg=typeof r.data.msg!=='undefined' ? r.data.msg : '';
            }else if(r.success===true){
                $refs.resetPasswordForm.reset()
                msg=typeof r.data.msg!=='undefined' ? r.data.msg : ''
                if (typeof r.data.redirect!=='undefined') {
                    window.location.href = r.data.redirect;
                }
            }
        })">

            <div class="form-group">
                <label for="pass1"><?php _e('New password', 'f-shop'); ?>:</label>
                <input type="password" name="pass1" id="pass1"
                       class="form-control"
                       title="<?php esc_attr_e('This field is required', 'f-shop'); ?>" 
                       autocomplete="new-password" required>
            </div>
            
            <div class="form-group">
                <label for="pass2"><?php _e('Confirm new password', 'f-shop'); ?>:</label>
                <input type="password" name="pass2" id="pass2"
                       class="form-control"
                       title="<?php esc_attr_e('This field is required', 'f-shop'); ?>" 
                       autocomplete="new-password" required>
            </div>
            
            <input type="hidden" name="rp_key" value="<?php echo esc_attr($rp_key); ?>">
            <input type="hidden" name="rp_login" value="<?php echo esc_attr($rp_login); ?>">
            
            <div class="form-group msg-success" x-html="msg" x-show="msg && msg.length>0"></div>
            <div class="form-group msg-error" x-show="errors && errors.length>0">
                <template x-for="error in errors">
                    <div x-text="error"></div>
                </template>
            </div>
            
            <div class="form-group">
                <button type="submit" class="bts bts-primary bts-lg"><?php _e('Reset password', 'f-shop'); ?></button>
            </div>
            
            <div class="fs-login-links justify-center">
                <a href="<?php the_permalink(13); ?>" class="fs-login-link">
                    <?php esc_html_e('Вернуться к форме входа', 'umbrella'); ?>
                </a>
            </div>
            
            <div class="clearfix"></div>
        </form>
        <?php
    }
} else {
    // Show request password reset form
    ?>
    <form method="post" class="fs-login-form"
          action="" x-ref="resetPasswordForm" x-data="{ errors: [], msg: '' }"
          x-on:submit.prevent="Alpine.store('FS').resetPassword($event).then((r)=>{
        if(r.success===false) {
            errors=typeof r.data.errors!=='undefined' ? r.data.errors : [];
            msg=typeof r.data.msg!=='undefined' ? r.data.msg : '';
        }else if(r.success===true){
            $refs.resetPasswordForm.reset()
            msg=typeof r.data.msg!=='undefined' ? r.data.msg : ''
            if (typeof r.data.redirect!=='undefined') {
                window.location.href = r.data.redirect;
            }
        }
    })">

        <div class="form-group">
            <label for="fs-user-email"><?php _e('E-mail', 'f-shop'); ?>:</label>
            <input type="email" name="fs_email" id="fs-user-email"
                   class="form-control"
                   title="<?php esc_attr_e('This field is required', 'f-shop'); ?>" required>
        </div>
        
        <div class="form-group msg-success" x-html="msg" x-show="msg.length>0"></div>
        
        <div class="form-group">
            <button type="submit" class="bts bts-primary bts-lg"><?php _e('Get a new password', 'f-shop'); ?></button>
        </div>
        
        <div class="fs-login-links justify-center">
            <a href="<?php the_permalink(13); ?>" class="fs-login-link">
                <?php esc_html_e('Вернуться к форме входа', 'umbrella'); ?>
            </a>
        </div>
        
        <div class="clearfix"></div>
    </form>
    <?php
}
?>