<input type="hidden" name="action" value="fs_login">
<div class="form-header">Для входа в личный кабинет необходимо пройти авторизацию. <a
    href="<?php echo add_query_arg( array( 'fs-page' => 'register' ), get_permalink( 2413 ) ) ?>">Регистрация</a></div>
<div class="form-info-login"></div>
<div class="form-group">
  <label for="inputEmail3" class="control-label"><?php _e( 'Email or login', 'fast-shop' ) ?></label>
  <input type="text" name="username" class="form-control" id="inputEmail3"
         placeholder="<?php _e( 'Email or login', 'fast-shop' ) ?>" required
         title="<?php _e( 'required', 'fast-shop' ) ?>">
</div>
<div class="form-group">
  <label for="inputPassword3" class="control-label"><?php _e( 'Password', 'fast-shop' ) ?></label>
  <input type="password" name="password" class="form-control" id="inputPassword3"
         placeholder="<?php _e( 'Password', 'fast-shop' ) ?>" required title="<?php _e( 'required', 'fast-shop' ) ?>">
</div>
<div class="form-group">
  <label class="checkbox-inline">
    <input type="checkbox"> <?php _e( 'Remember me', 'fast-shop' ) ?>
  </label>

</div>
<div class="form-group">
  <button type="submit" class="button-t1"><?php _e( 'Log', 'fast-shop' ) ?> <img
      src="/wp-content/plugins/f-shop/assets/img/heart.svg" alt="preloader" class="fs-preloader"></button>
</div>
<div class="clearfix"></div>

