<h3><?php _e( 'Forgot your password?', 'f-shop' ) ?></h3>

<div class="form-group">
    <label for="fs-user-login"><?php _e( 'E-mail', 'f-shop' ) ?>:</label>
    <input type="email" name="user_login" id="fs-user-login" class="form-control"
           title="<?php esc_attr_e( 'This field is required', 'f-shop' ) ?>" required>
</div>

<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI'] ?>">

<button type="submit" class="btn btn-primary"><?php _e( 'Get a new password', 'f-shop' ) ?></button>
