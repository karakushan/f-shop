<form name="fs-order-send" action="#" class="fs-single-order <?php echo $class ?>">
	<input type="hidden" name="action" value="order_send">
	<input type="hidden" name="fs_cart[product_id]" value="<?php echo $product_id ?>">
	<input type="hidden" name="order_type" value="single">
	<?php wp_nonce_field('fast-shop'); ?>
	<div class="form-group">
		<input type="text" name="fs_name" class="form-control" placeholder="Имя"  title="заполните поле" required>
	</div>
	<div class="form-group">
		<input type="tel" name="fs_phone" class="form-control" placeholder="Телефон" title="заполните поле"  required>
	</div>
	<div class="form-group">
		<div class="count">
			<input type="number" name="fs_cart[count]" value="1" min="1"> шт.
		</div>
	</div>
	<?php fs_order_send('Купить','class="send-button"'); ?>
</form>