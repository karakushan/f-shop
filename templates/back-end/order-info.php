<div class="wrap">
	<h1>Данные заказа #<?php echo $_GET['id'] ?></h1>
	<div id="fs-table" class="fs-content-wrapper fs-order-info" >
		<table>
			<thead>
			<tr>
				<th>#ID товара</th>
				<th>Название товара</th>
				<th>Артикул</th>
				<th>Количество</th>
				<th>Цена</th>
			</tr>
			</thead>
			<?php foreach ($products as $key => $order): ?>
				<tr>
					<td><a href="<?php echo admin_url('post.php?post='.$order->post_id.'&action=edit') ?>" title="ссылка на редактирование товара"><?php echo $order->post_id ?></a></td>
					<td><a href="<?php echo get_permalink($order->post_id) ?>" target="_blank" title="смотреть товар на сайте"><?php echo get_the_title($order->post_id); ?></a></td>
					<td><?php echo fs_product_code($order->post_id) ?></td>
					<td><?php echo $order->count ?></td>
					<td><?php fs_the_price($order->post_id)?></td>
				</tr>
			<?php endforeach ?>
			<tr class="all_summ">
				<td colspan="4">Общая сумма</td>
				<td><?php echo $orders->fs_order_total($order_id) ?></td>
			</tr>


		</table>
	</div>
	</form>
</div>