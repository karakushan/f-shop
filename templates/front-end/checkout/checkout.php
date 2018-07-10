<div class="form-group">
	<?php fs_form_field( 'fs_first_name' ) ?>
</div>
<div class="form-group">
	<?php fs_form_field( 'fs_last_name' ) ?>
</div>
<div class="form-group">
	<?php fs_form_field( 'fs_email' ) ?>
</div>
<div class="form-group">
	<?php fs_form_field( 'fs_phone' ) ?>
</div>
<div class="form-group">
	<?php fs_form_field( 'fs_city' ) ?>
</div>
<div class="form-group">
	<?php fs_form_field( 'fs_delivery_number' ) ?>
</div>
<div class="form-group">
	<?php fs_form_field( 'fs_comment' ) ?>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="form-group">
		<?php fs_form_field( 'fs_payment_methods' ) ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="form-group">
		<?php fs_form_field( 'fs_delivery_methods' ) ?>
    </div>
  </div>
</div>
<?php if ( ! is_user_logged_in() ): ?>
  <div class="form-group">
	  <?php fs_form_field( 'fs_customer_register' ) ?>
  </div>
<?php endif ?>
<p class="text-center">
	<?php fs_order_send(); ?>
</p>