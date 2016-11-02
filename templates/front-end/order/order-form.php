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
<div class="row">
	<div class="col-md-10">
		<div class="form-group">
			<label for="">Товар</label>
			<input type="text" class="form-control" name="fs_cart[product_name]" value="" readonly>
			<input type="hidden" class="form-control" name="fs_cart[product_id]" value="">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<label for="">К-во</label>
			<input type="number" class="form-control" name="fs_cart[count]" value="1" min="1" required title="мин 1 шт.">
		</div>
	</div>
	
</div>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="">Ф.И.О</label>
			<input type="text" class="form-control" name="fs_name" required title="заполните поле">
		</div>
	</div>
	
</div>
<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label for="">Телефон</label>
			<input type="text" class="form-control" name="fs_phone" required title="заполните поле">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label>E-mail</label>
			<input type="email" class="form-control"  name="fs_email" required title="нужен валидный e-mail адрес">
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
						<option value="<?php echo $shipping->name  ?>"><?php echo $shipping->name  ?></option>
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
						<option value="<?php echo $payment->name  ?>"><?php echo $payment->name  ?></option>
					<?php endforeach ?>
				<?php endif ?>
			</select>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<label for="">Адрес доставки</label>
			<input type="text" class="form-control" name="fs_adress" required title="необходим адрес куда доставить товар">
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
<div class="row">
	<div class="col-md-12">
		<div class="form-group text-center">
		<input type="checkbox"  name="fs_register_user" id="fs_register_user" value="1" checked="checked">
			<label for="fs_register_user">зарегистрироваться на сайте</label>
			
		</div>
	</div>

</div>
<div class="text-center"><?php fs_order_send('Заказ подтверждаю'); ?></div>