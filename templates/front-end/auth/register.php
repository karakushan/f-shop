<div class="form-group">
    <label for="fs_first_name"><?php esc_html_e( 'Your name', 'f-shop' ) ?><sup>*</sup></label>
	<?php fs_form_field( 'fs_first_name', [ 'class' => 'form-control', 'required' => true ] ); ?>
</div>

<div class="form-group">
    <label for="fs_email"><?php esc_html_e( 'Email', 'f-shop' ) ?><sup>*</sup></label>
	<?php fs_form_field( 'fs_email', [ 'class' => 'form-control', 'required' => true ] ); ?>
</div>

<div class="form-group">
    <label for="fs_password"><?php esc_html_e( 'Password', 'f-shop' ) ?><sup>*</sup></label>
	<?php fs_form_field( 'fs_password', [ 'class' => 'form-control', 'required' => true ] ); ?>
</div>

<div class="form-group">
    <label for="fs_repeat_password"><?php esc_html_e( 'Repeat password', 'f-shop' ) ?><sup>*</sup></label>
    <?php fs_form_field( 'fs_repeat_password', [ 'class' => 'form-control', 'required' => true ] ); ?>
</div>

<div class="form-group fs-login-bottom">
    <div class="remember-me checkbox-orange">
        <input type="checkbox" name="fs-rules" id="fs-rules" required title="<?php  esc_html_e('Must agree','f-shop')  ?>" checked>
        <label for="fs-rules"><?php esc_html_e( 'I agree to the terms of use and the processing of my personal data', 'f-shop' ) ?></label>
    </div>
</div>

<div class="form-group">
    <?php  fs_form_submit(__( 'Send', 'f-shop' ))  ?>
</div>

<div class="form-group fs-login-bottom">
    <a href="<?php the_permalink( fs_option( 'page_auth' ) ) ?>"><?php esc_html_e( 'Have an account?', 'f-shop' ) ?></a>
</div>