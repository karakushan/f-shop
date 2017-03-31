<div class="wrap" id="fs-order-info">
	<h1>Данные заказа #<?php echo (int)$_GET['id'] ?></h1>
	<h3>Информация о купленных товарах</h3>
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<th>#ID товара</th>
				<th>Название товара</th>
				<th>Артикул</th>
				<th>Количество</th>
				<th>Цена</th>
			</tr>
		</thead>
		<?php foreach ($order->products as $key => $o): ?>
			<tr>
				<td><a href="<?php echo admin_url('post.php?post='.$key.'&action=edit') ?>" title="ссылка на редактирование товара"><?php echo $key ?></a></td>
				<td><a href="<?php echo get_permalink($key) ?>" target="_blank" title="смотреть товар на сайте"><?php echo get_the_title($key); ?></a></td>
				<td><?php echo fs_product_code($key) ?></td>
				<td><?php echo $o['count'] ?></td>
				<td><?php fs_the_price($key)?></td>
			</tr>
		<?php endforeach ?>
		<tr class="all_summ">
			<td colspan="4">Общая сумма</td>
			<td><?php echo $order->summa ?> <?php echo fs_currency() ?></td>
		</tr>
	</table>
	<h3>Данные пользователя</h3>
	<table class="wp-list-table widefat fixed striped posts">
		<tr>
			<th>#ID пользователя</th><td><?php echo $order->user_id ?></td>
		</tr>
		<tr>
			<th>Имя</th><td><?php echo $order->first_name; ?></td>
		</tr>
        <tr>
			<th>Email</th><td><?php echo $order->email; ?></td>
		</tr>
		<tr>
			<th>Телефон</th><td><?php echo $order->phone; ?></td>
		</tr>
		<tr>
			<th>Город </th><td><?php echo $order->city; ?></td>
		</tr>
		<tr>
			<th>Способ доставки</th><td><?php echo $order->delivery_name; ?></td>
		</tr>
		<tr>
			<th>№ отделения</th><td><?php echo $order->delivery_number; ?></td>
		</tr>
		<tr>
			<th>Способ оплаты</th><td><?php echo $order->payment_name; ?></td>
		</tr>
        <tr>
			<th>Комментарий</th><td><?php echo $order->comments; ?></td>
		</tr>
	</table>
</div>