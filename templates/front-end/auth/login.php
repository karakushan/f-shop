<div class="form-group">
    <label for="fs-login-field" class="control-label"><?php esc_html_e( 'Email or login', 'f-shop' ) ?></label>
    <input type="text" name="username" class="form-control" id="fs-login-field" required
           title="<?php esc_html_e( 'required', 'f-shop' ) ?>" autocomplete="off">
</div>

<div class="form-group">
    <label for="fs-password-field" class="control-label"><?php esc_html_e( 'Password', 'f-shop' ) ?></label>
    <input type="password" name="password" class="form-control" id="fs-password-field" required
           title="<?php esc_html_e( 'required', 'f-shop' ) ?>" autocomplete="off">
</div>

<div class="form-group">
    <button type="submit" class="btn btn-success btn-lg"><?php esc_html_e( 'Login', 'f-shop' ) ?></button>
</div>

<div class="fs-login-bottom">
    <a href="<?php the_permalink( fs_option( 'page_register' ) ) ?>"><?php esc_html_e( 'Registration', 'f-shop' ) ?></a>
    |
    <a href="<?php the_permalink( fs_option( 'page_lostpassword' ) ) ?>"><?php esc_html_e( 'Forgot your password?', 'f-shop' ) ?></a>
</div>

<div class="clearfix"></div>

