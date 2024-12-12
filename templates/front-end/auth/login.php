<form method="post" class="fs-login-form" action="" x-data="{ errors: [], user: { username: '' , password: ''} }"
      x-on:submit.prevent="Alpine.store('FS').login(user).then((r)=>{
    if(r.success===false) {
        errors.any=r.data.msg;
        msg=r.data.msg;
    }else if(r.success===true){
         msg=r.data.msg
         if (typeof r.data.redirect!=='undefined') {
             window.location.href = r.data.redirect;
         }
    }
})">

    <div class="alert alert-danger" x-show="errors.any" x-text="errors.any"></div>

    <div class="form-group">
        <label for="fs-login-field" class="control-label"><?php esc_html_e( 'Email or login', 'f-shop' ) ?></label>
        <input type="text" x-model="user.username" name="username" class="form-control" id="fs-login-field"
               title="<?php esc_html_e( 'required', 'f-shop' ) ?>">
    </div>

    <div class="form-group">
        <label for="fs-password-field" class="control-label"><?php esc_html_e( 'Password', 'f-shop' ) ?></label>
        <input x-model="user.password" type="password" name="password" class="form-control" id="fs-password-field"
               title="<?php esc_html_e( 'required', 'f-shop' ) ?>">
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success btn-lg"><?php esc_html_e( 'Login', 'f-shop' ) ?></button>
    </div>
</form>