<form method="post" class="fs-login-form w-[400px] max-w-full shadow-lg mx-auto p-[30px] space-y-5 rounded-2xl bg-white"
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
    <h3><?php _e( 'Forgot your password?', 'f-shop' ) ?></h3>

    <div class="form-group">
        <label for="fs-user-login"><?php _e( 'E-mail', 'f-shop' ) ?>:</label>
        <input type="email" name="user_login" id="fs-user-login"
               class="form-control h-[60px] rounded-[30px] w-full border px-5"
               title="<?php esc_attr_e( 'This field is required', 'f-shop' ) ?>" required>
    </div>
    <div class="form-group text-green-500 text-center" x-html="msg" x-show="msg.length>0">

    </div>
    <div class="form-group">
        <button type="submit"
                class="btn btn-primary w-full bg-theme-blue1 text-xl font-semibold px-[30px] py-[18px] leading-6 text-white rounded-[35px] inline-flex items-center justify-center"><?php _e( 'Get a new password', 'f-shop' ) ?></button>
    </div>
</form>