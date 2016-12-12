<?php 
/*
Список основных полей для использования в письмах
fs_name
fs_email
fs_city
fs_delivery
fs_pay
fs_phone
fs_adress
fs_message
*/
?>
<?php $user=wp_get_current_user(); ?>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="">Ф.И.О</label>
			<input type="text" class="form-control" name="fs_name" value="<?php echo  get_user_meta($user->ID,'fs-full-name',1) ?>" required title="заполните поле">
		</div>
	</div>
	
</div>
<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label for="">Телефон</label>
			<input type="text" class="form-control" value="<?php echo  get_user_meta($user->ID,'fs-phone',1) ?>" name="fs_phone" required title="заполните поле">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label>E-mail</label>
			<input type="email" class="form-control" value="<?php echo $user->user_email ?>"  name="fs_email" required title="нужен валидный e-mail адрес">
		</div>
	</div>

</div>
<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label for="">Выберите способ доставки</label>
			<select class="form-control" name="fs_delivery" title="<?php _e('Choose a shipping method', 'fast-shop' ); ?>" required>
				<option value=""><?php _e('Shipping method', 'fast-shop' ); ?></option>
				<?php $shipping_methods=get_terms('fs-delivery-methods',array('hide_empty'=>false)) ?>
				<?php if ($shipping_methods): ?>
					<?php foreach ($shipping_methods as $key => $shipping): ?>
						<option value="<?php echo $shipping->name  ?>" <?php selected($shipping->term_id,get_user_meta($user->ID,'fs-delivery',1)) ?>><?php echo $shipping->name  ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label for="">Выберите способ оплаты
			</label>
			<select class="form-control" name="fs_pay" title="<?php _e('Select a payment method', 'fast-shop' ); ?>" required>
				<option value=""><?php _e('Type of payment', 'fast-shop' ); ?></option>
				<?php $payment_methods=get_terms('fs-payment-methods',array('hide_empty'=>false)) ?>
				<?php if ($payment_methods): ?>
					<?php foreach ($payment_methods as $key => $payment): ?>
						<option value="<?php echo $payment->name  ?>" <?php selected($payment->term_id,get_user_meta($user->ID,'fs-payment',1)) ?>><?php echo $payment->name  ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
</div>
<div class="form-group">
        <label for="fs-city"><?php _e('City','fast-shop') ?></label>
        <input type="text" name="fs_city"  class="form-control" id="fs-city" value="<?php echo  get_user_meta($user->ID,'fs-city',1) ?>">
    </div>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="">Адрес доставки</label>
			<input type="text" class="form-control" value="<?php echo  get_user_meta($user->ID,'fs-adress',1) ?>" name="fs_adress" required title="необходим адрес куда доставить товар">
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="">Комментарий к заказу</label>
			<textarea class="form-control" name="fs_message" ></textarea>
		</div>
	</div>

</div>
<!-- <div class="row">
	<div class="col-md-12">
		<div class="form-group text-center">
		<input type="checkbox"  name="fs_register_user" id="fs_register_user" value="1" checked="checked">
			<label for="fs_register_user">зарегистрироваться на сайте</label>
			
		</div>
	</div>

</div> -->
<div class="text-center"><?php fs_order_send('Заказ подтверждаю','','<img src="/wp-content/plugins/fast-shop/assets/img/heart.svg" alt="preloader">'); ?></div>