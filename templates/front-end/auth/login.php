<?php global $fs_config; ?>
<div class="form-group">
    <label for="inputEmail3" class="control-label"><?php esc_html_e('Email or login', 'f-shop') ?></label>
    <input type="text" name="username" class="form-control" id="inputEmail3"
           placeholder="<?php esc_html_e('Email or login', 'f-shop') ?>" required
           title="<?php esc_html_e('required', 'f-shop') ?>" autocomplete="off">
</div>
<div class="form-group">
    <label for="inputPassword3" class="control-label"><?php esc_html_e('Password', 'f-shop') ?></label>
    <input type="password" name="password" class="form-control" id="inputPassword3"
           placeholder="<?php esc_html_e('Password', 'f-shop') ?>" required
           title="<?php esc_html_e('required', 'f-shop') ?>" autocomplete="off">
</div>
<div class="form-group">
    <button type="submit" class="fs-submit fs-order-send btn btn-success btn-lg"><?php esc_html_e('Log', 'f-shop') ?>
        <img
                src="<?php echo esc_url($fs_config->data['preloader']) ?>" alt="preloader" class="fs-preloader">
    </button>
</div>
<div class="clearfix"></div>

