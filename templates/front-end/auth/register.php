<div class="form-group">
    <label for="fs-full-name"><?php esc_html_e( 'Your name', 'f-shop' ) ?><sup>*</sup></label>
    <input type="text" name="fs-form[fs-full-name]" class="form-control" id="fs-full-name" required
           title="<?php esc_attr_e( 'required field', 'f-shop' ) ?>">
</div>

<div class="form-group">
    <label for="fs-email"><?php esc_html_e( 'Email', 'f-shop' ) ?><sup>*</sup></label>
    <input type="email" name="fs-email" class="form-control" id="fs-email" required
           title="<?php esc_attr_e( 'enter a valid email', 'f-shop' ) ?>">
</div>

<div class="form-group">
    <label for="fs-password"><?php esc_html_e( 'Password', 'f-shop' ) ?><sup>*</sup></label>
    <input type="password" name="fs-password" class="form-control" id="fs-password" required
           title="<?php esc_attr_e( 'required field', 'f-shop' ) ?>">
</div>

<div class="form-group">
    <label for="fs-repassword"><?php esc_html_e( 'Repeat password', 'f-shop' ) ?><sup>*</sup></label>
    <input type="password" name="fs-repassword" class="form-control" id="fs-repassword"
           title="<?php esc_attr_e( 'password and repeat password must match', 'f-shop' ) ?>" required>
</div>

<div class="form-group">
    <button type="submit" class="btn btn-success btn-lg"><?php esc_html_e( 'Send', 'f-shop' ) ?></button>
</div>

<div class="fs-login-bottom">
    <a href="<?php the_permalink( fs_option( 'page_auth' ) ) ?>"><?php esc_html_e( 'Have an account?', 'f-shop' ) ?></a>
</div>