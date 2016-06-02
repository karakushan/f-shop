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
	select.status_0 {
		background: #294AFF;
		color: #fff;
	}
	select.status_2 {
		background: #7CFF82;
	}
	select.status_3 {
		background: #E61E1E;
		color: #fff;
	}
	select.status_1 {
		background: #FFD042;
	}
	.order-paggination li {
		display: inline;
	}	
	.order-paggination a {
		padding: 4px 9px;
		margin-right: 5px;
		background: #23282D;
		color: #fff;
		text-decoration: none;
		border-radius: 2px;
	}
	.order-paggination a:hover, .order-paggination a.active{
    background: #E91E63;
}
</style>
<div class="wrap">
	<h2>Управление заказами</h2>
	<table>
		<tr>
			<th>#ID</th><th>Дата заказа</th><th>Телефон</th><th>Email</th><th>Способ доставки</th><th>Статус/Изменить</th><th>Сумма</th><th>Подробности</th><!-- <th>Отмена</th> -->
		</tr>
		<?php // print_r($orders->get_orders()) ?>
		<?php foreach ($orders->get_orders() as $order): ?>
			<?php $status=$orders->order_status($order->status); ?>
			<tr>
				<td><?php echo $order->id ?></td>
				<td><?php echo date('d.m.Y H:i',strtotime($order->date)) ?></td>
				<td><?php echo $order->telephone ?></td>
				<td><?php echo $order->email ?></td>
				<td><?php echo $delivery->delivery[$order->delivery]['name'] ?></td>
				<td><select name="" id="" class="status_<?php echo $order->status ?>"  onchange="if(confirm('Вы дейтвительно хотите поменять статус заказа <?php echo $order->id ?>')){ document.location=this.value}">
					<?php foreach ($orders->order_status as $key => $value): ?>
						<option value="<?php echo add_query_arg(array('action'=>'edit','id'=>$order->id,'status'=>$key)) ?>" <?php selected( $key, $order->status  ); ?>><?php echo $value ?></option>
					<?php endforeach ?>

				</select></td>
				<td><?php echo $order->summa.' '.get_option( 'currency_icon' ) ?></td>
				<td><a href="<?php echo add_query_arg(array('action'=>'info','id'=>$order->id)) ?>">Подробности</a></td>
				<!-- <td><a href="<?php echo add_query_arg(array('action'=>'cancel','id'=>$order->id)) ?>">Отмена</a></td> -->
			</tr>
		<?php endforeach ?>

	</table>
	<?php $orders->order_pagination('order-paggination') ?>
</div>