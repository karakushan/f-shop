<div class="wrap">
	<h1>Данные заказа #<?php echo $_GET['id'] ?></h1>
	<div id="fs-table" class="fs-content-wrapper fs-order-info" >
		<table>
			<thead>
				<tr>
					<th>Список товаров</th>
					<th>Количество</th>
					<th>Цена</th>
				
				</tr>
			</thead>
			<?php foreach ($products as $key => $value): ?>


				<tr>
					<td><a href="<?php echo get_permalink($key) ?>" target="_blank"><?php echo get_the_title($key); ?></a></td>
					<td><?php echo $value['count'] ?></td>
					<td><?php fs_the_price($key)?></td>

				</tr>
			<?php endforeach ?>
			<tr class="all_summ">
				<td colspan="2">Общая сумма</td>
				<td><?php echo $order_info->summa.' '.get_option( 'currency_icon' ) ?></td>
			</tr>


		</table>
	</div>
</form>
</div>