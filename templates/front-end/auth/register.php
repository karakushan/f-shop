<form method="post" class="fs-login-form" action="" x-ref="registerForm" x-data="{ errors: [], msg: '' }"
      x-on:submit.prevent="Alpine.store('FS').register($event).then((r)=>{
    if(r.success===false) {
        errors=typeof r.data.errors!=='undefined' ? r.data.errors : [];
        msg=typeof r.data.msg!=='undefined' ? r.data.msg : '';
    }else if(r.success===true){
    $refs.registerForm.reset()
         msg=typeof r.data.msg!=='undefined' ? r.data.msg : ''
         if (typeof r.data.redirect!=='undefined') {
             window.location.href = r.data.redirect;
         }
    }
})">
    <div x-text="JSON.stringify()"></div>
    <div class="w-[400px] max-w-full shadow-lg mx-auto p-[30px] space-y-5 rounded-2xl bg-white">
        <div class="form-group" x-bind:class="{'has-error': errors.fs_first_name}">
            <label for="fs_first_name"><?php esc_html_e( 'Your name', 'f-shop' ) ?><sup>*</sup></label>
			<?php fs_form_field( 'fs_first_name', [
				'class'    => 'h-[60px] rounded-[30px] w-full border px-5',
				'required' => false
			] ); ?>
            <span class="font-light text-sm text-red-500 mt-1 block w-full"
                  x-text="errors.fs_first_name" x-show="errors.fs_first_name" style="display: none;"></span>
        </div>

        <div class="form-group">
            <label for="fs_email"><?php esc_html_e( 'Email', 'f-shop' ) ?><sup>*</sup></label>
			<?php fs_form_field( 'fs_email', [
				'class'    => 'h-[60px] rounded-[30px] w-full border px-5',
				'required' => false
			] ); ?>
            <span class="font-light text-sm text-red-500 mt-1 block w-full"
                  x-text="errors.fs_email" x-show="errors.fs_email" style="display: none;"></span>
        </div>

        <div class="form-group">
            <label for="fs_password"><?php esc_html_e( 'Password', 'f-shop' ) ?><sup>*</sup></label>
			<?php fs_form_field( 'fs_password', [
				'class'    => 'h-[60px] rounded-[30px] w-full border px-5',
				'required' => false
			] ); ?>
            <span class="font-light text-sm text-red-500 mt-1 block w-full"
                  x-text="errors.fs_password" x-show="errors.fs_password" style="display: none;"></span>
        </div>

        <div class="form-group">
            <label for="fs_repeat_password"><?php esc_html_e( 'Repeat password', 'f-shop' ) ?><sup>*</sup></label>
			<?php fs_form_field( 'fs_repeat_password', [
				'class'    => 'h-[60px] rounded-[30px] w-full border px-5',
				'required' => false
			] ); ?>
            <span class="font-light text-sm text-red-500 mt-1 block w-full"
                  x-text="errors.fs_repeat_password" x-show="errors.fs_repeat_password" style="display: none;"></span>
        </div>

        <div class="form-group fs-login-bottom">
            <div class="remember-me checkbox-orange">
                <input type="checkbox" name="fs_rules" id="fs-rules" required
                       title="<?php esc_html_e( 'Must agree', 'f-shop' ) ?>" checked>
                <label for="fs-rules"><?php esc_html_e( 'I agree to the terms of use and the processing of my personal data', 'f-shop' ) ?></label>
            </div>
        </div>

        <div x-show="msg.length>0" x-html="msg" class="text-green-500 form-group register-success">

        </div>

        <div class="form-group">
			<?php fs_form_submit( __( 'Send', 'f-shop' ), [ 'class' => 'bsm:w-auto w-full bg-theme-blue1 text-xl font-semibold px-[30px] py-[18px] leading-6 text-white rounded-[35px] inline-flex items-center justify-center' ] ) ?>
        </div>

        <div class="form-group fs-login-bottom text-center">
            <a href="<?php the_permalink( fs_option( 'page_auth' ) ) ?>"><?php esc_html_e( 'Have an account?', 'f-shop' ) ?></a>
        </div>
    </div>
</form>