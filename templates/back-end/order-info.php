<style>
	table, table td, table th {
		border: 2px dashed rgb(241, 241, 241);
		border-collapse: collapse;
		padding: 6px 16px;
		/* border-radius: 10px; */
		background: #ccc;
	}
	table th{
		background: #00b9eb;
		font-weight: bold;
		color: #000;
	}
	.all_summ {
		text-transform: uppercase;
		font-weight: bold;
		font-size: 14px;
	}
	.all_summ td {
		background: #B3B3B3;
	}
</style>
<div class="wrap">
	<h2>Данные заказа #<?php echo $_GET['id'] ?></h2>
	<table>
		<tr>
			<th>Список товаров</th>
			<th>Количество</th>
			<th>Цена</th>
			
		</tr>
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

</form>
</div>