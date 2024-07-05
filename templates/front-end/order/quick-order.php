<div class="fs-quick-order__row">
    <label for="first-name"><?php _e( 'First Name', 'f-shop' ); ?></label>
    <?php fs_form_field('fs_first_name'); ?>
</div>
<div class="fs-quick-order__row">
    <label for="first-name"><?php _e( 'Phone', 'f-shop' ); ?></label>
    <?php fs_form_field('fs_phone'); ?>
</div>

<div class="alert alert-danger" x-show="msg" x-text="msg"></div>

<div>
    <?php fs_order_send(__( 'Send', 'f-shop' )); ?>
</div>
