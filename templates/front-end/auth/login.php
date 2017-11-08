<div class="fs-modal-close" data-fs-action="modal-close" title="закрыть окно">x</div>
<?php global $fs_config;?>
<div class="form-group">
  <label for="inputEmail3" class="control-label"><?php _e( 'Email or login', 'fast-shop' ) ?></label>
  <input type="text" name="username" class="form-control" id="inputEmail3"
         placeholder="<?php _e( 'Email or login', 'fast-shop' ) ?>" required
         title="<?php _e( 'required', 'fast-shop' ) ?>" autocomplete="off">
</div>
<div class="form-group">
  <label for="inputPassword3" class="control-label"><?php _e( 'Password', 'fast-shop' ) ?></label>
  <input type="password" name="password" class="form-control" id="inputPassword3"
         placeholder="<?php _e( 'Password', 'fast-shop' ) ?>" required title="<?php _e( 'required', 'fast-shop' ) ?>" autocomplete="off">
</div>
<div class="form-group">
  <button type="submit" class="fs-submit"><?php _e( 'Log', 'fast-shop' ) ?> <img
      src="<?php echo $fs_config->data['preloader'] ?>" alt="preloader" class="fs-preloader"></button>
</div>
<div class="clearfix"></div>

